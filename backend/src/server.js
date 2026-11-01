require('dotenv').config();
const bcrypt = require('bcryptjs');
const cors = require('cors');
const express = require('express');
const jwt = require('jsonwebtoken');
const pool = require('./database');

const app = express();
const port = Number(process.env.PORT || 8080);
const host = process.env.HOST || '127.0.0.1';
const jwtSecret = process.env.JWT_SECRET || '';
const allowedOrigins = (process.env.CORS_ORIGINS || '')
  .split(',')
  .map((origin) => origin.trim())
  .filter(Boolean);

class HttpError extends Error {
  constructor(status, message) {
    super(message);
    this.status = status;
  }
}

app.disable('x-powered-by');
app.use(cors({
  origin(origin, callback) {
    if (!origin || allowedOrigins.includes(origin)) return callback(null, true);
    return callback(new HttpError(403, 'Origin is not allowed by CORS.'));
  },
  allowedHeaders: ['Authorization', 'Content-Type'],
  methods: ['GET', 'POST', 'PATCH', 'PUT', 'DELETE', 'OPTIONS'],
}));
app.use(express.json({ limit: '1mb' }));

function asyncRoute(handler) {
  return (req, res, next) => Promise.resolve(handler(req, res, next)).catch(next);
}

function requireAuth(req, _res, next) {
  const authorization = req.get('authorization') || '';
  const token = authorization.startsWith('Bearer ')
    ? authorization.slice(7)
    : '';
  try {
    if (!token || !jwtSecret) throw new Error('Missing token or JWT secret.');
    req.rider = jwt.verify(token, jwtSecret);
    next();
  } catch {
    next(new HttpError(401, 'Sign in again to continue.'));
  }
}

function requireString(value, name, maximum = 500) {
  if (typeof value !== 'string' || value.trim().length === 0 || value.length > maximum) {
    throw new HttpError(400, `${name} is required.`);
  }
  return value.trim();
}

function numeric(value, name) {
  const result = Number(value);
  if (!Number.isFinite(result)) throw new HttpError(400, `${name} must be a number.`);
  return result;
}

function cents(value) {
  return Math.round(Number(value) * 100);
}

async function getParcel(connection, riderId, trackingNo, lock = false) {
  const [rows] = await connection.execute(
    `SELECT * FROM parcels WHERE tracking_no = ? AND rider_profile_id = ?${lock ? ' FOR UPDATE' : ''}`,
    [trackingNo, riderId],
  );
  if (!rows.length) throw new HttpError(404, 'Parcel was not found.');
  return rows[0];
}

const transitions = {
  ready_for_pickup: new Set(['assigned']),
  assigned: new Set(['picked_up']),
  picked_up: new Set(['at_hub']),
  at_hub: new Set(['out_for_delivery']),
  out_for_delivery: new Set(['delivered', 'failed_delivery', 'return_to_sender', 'at_hub']),
  failed_delivery: new Set(['at_hub', 'return_to_sender']),
  return_to_sender: new Set(['returned']),
  delivered: new Set(),
  returned: new Set(),
};

async function updateStatus(connection, parcel, target, reason) {
  const now = new Date();
  const updates = {
    status: target,
    updated_at: now,
    synced: true,
  };
  if (target === 'assigned') updates.assigned_at = now;
  if (target === 'picked_up') {
    updates.picked_up_at = now;
    updates.pickup_fee = 30;
  }
  if (target === 'out_for_delivery') updates.out_for_delivery_at = now;
  if (target === 'failed_delivery') {
    updates.failure_reason = reason;
    updates.delivery_attempts = Number(parcel.delivery_attempts) + 1;
  }
  if (target === 'delivered') {
    updates.delivered_at = now;
    updates.delivery_fee = 50;
  }
  const fields = Object.keys(updates);
  await connection.execute(
    `UPDATE parcels SET ${fields.map((field) => `\`${field}\` = ?`).join(',')} WHERE id = ?`,
    [...Object.values(updates), parcel.id],
  );
  await connection.execute(
    'INSERT INTO parcel_status_logs (parcel_id,from_status,to_status,reason,created_at) VALUES (?,?,?,?,?)',
    [parcel.id, parcel.status, target, reason || null, now],
  );
  return { ...parcel, ...updates };
}

async function bootstrap(riderId) {
  const [profile] = await pool.execute(
    'SELECT * FROM rider_profile WHERE id = ? LIMIT 1',
    [riderId],
  );
  if (!profile.length) throw new HttpError(401, 'Rider profile is unavailable.');
  const [
    [parcels],
    [statusLogs],
    [offers],
    [remittances],
    [notifications],
    [conversations],
    [messages],
    [emergencyContacts],
  ] = await Promise.all([
    pool.execute('SELECT * FROM parcels WHERE rider_profile_id = ? ORDER BY updated_at DESC', [riderId]),
    pool.execute(`SELECT l.* FROM parcel_status_logs l
      JOIN parcels p ON p.id = l.parcel_id
      WHERE p.rider_profile_id = ? ORDER BY l.created_at DESC`, [riderId]),
    pool.execute('SELECT * FROM job_offers WHERE rider_profile_id = ? ORDER BY expires_at', [riderId]),
    pool.execute('SELECT * FROM cod_remittances WHERE rider_profile_id = ? ORDER BY created_at DESC', [riderId]),
    pool.execute('SELECT * FROM notifications WHERE rider_profile_id = ? ORDER BY created_at DESC', [riderId]),
    pool.execute('SELECT * FROM conversations WHERE rider_profile_id = ? ORDER BY last_message_at DESC', [riderId]),
    pool.execute(`SELECT m.* FROM messages m
      JOIN conversations c ON c.id = m.conversation_id
      WHERE c.rider_profile_id = ? ORDER BY m.created_at`, [riderId]),
    pool.execute('SELECT * FROM emergency_contacts WHERE rider_profile_id = ? ORDER BY id', [riderId]),
  ]);
  return {
    profile: profile[0],
    parcels,
    status_logs: statusLogs,
    offers: profile[0].is_active && profile[0].kyc_status !== 'suspended'
      ? offers
      : [],
    remittances,
    notifications,
    conversations,
    messages,
    emergency_contacts: emergencyContacts,
    outbox: [],
  };
}

app.get('/api/health', asyncRoute(async (_req, res) => {
  await pool.query('SELECT 1');
  res.json({ status: 'ok', database: 'mysql' });
}));

app.post('/api/auth/login', asyncRoute(async (req, res) => {
  if (!jwtSecret || jwtSecret.length < 32) {
    throw new HttpError(503, 'Set a random JWT_SECRET of at least 32 characters.');
  }
  const email = requireString(req.body?.email, 'Email', 254).toLowerCase();
  const password = requireString(req.body?.password, 'Password', 1024);
  const [rows] = await pool.execute(
    `SELECT a.id AS account_id, a.password_hash, p.*
     FROM rider_accounts a
     JOIN rider_profile p ON p.id = a.rider_profile_id
     WHERE a.email = ? LIMIT 1`,
    [email],
  );
  if (!rows.length || !(await bcrypt.compare(password, rows[0].password_hash))) {
    throw new HttpError(401, 'Email or password is incorrect.');
  }
  const rider = rows[0];
  if (!rider.is_active) throw new HttpError(403, 'This rider account is inactive.');
  const token = jwt.sign(
    { sub: String(rider.account_id), riderId: Number(rider.id) },
    jwtSecret,
    { expiresIn: '1h', issuer: 'mimoo-rider-api' },
  );
  res.json({ token, profile: rider });
}));

app.use('/api', requireAuth);

app.get('/api/bootstrap', asyncRoute(async (req, res) => {
  res.json(await bootstrap(req.rider.riderId));
}));

app.patch('/api/profile', asyncRoute(async (req, res) => {
  const allowed = ['phone', 'address', 'photo_path'];
  const updates = {};
  for (const field of allowed) {
    if (Object.hasOwn(req.body || {}, field)) {
      updates[field] = requireString(req.body[field], field, 500);
    }
  }
  if (!Object.keys(updates).length) throw new HttpError(400, 'No profile fields were provided.');
  const fields = Object.keys(updates);
  await pool.execute(
    `UPDATE rider_profile SET ${fields.map((field) => `\`${field}\` = ?`).join(',')} WHERE id = ?`,
    [...Object.values(updates), req.rider.riderId],
  );
  const data = await bootstrap(req.rider.riderId);
  res.json(data.profile);
}));

app.put('/api/profile/check-in', asyncRoute(async (req, res) => {
  const [rows] = await pool.execute(
    'SELECT is_active,kyc_status FROM rider_profile WHERE id = ?',
    [req.rider.riderId],
  );
  if (!rows.length || !rows[0].is_active || rows[0].kyc_status === 'suspended') {
    throw new HttpError(403, 'This rider cannot change shift status.');
  }
  await pool.execute(
    'UPDATE rider_profile SET checked_in_at = ? WHERE id = ?',
    [req.body?.checked_in ? new Date() : null, req.rider.riderId],
  );
  res.json({ checked_in: Boolean(req.body?.checked_in) });
}));

app.patch('/api/offers/:id', asyncRoute(async (req, res) => {
  const id = Number(req.params.id);
  const state = requireString(req.body?.state, 'state', 16);
  if (!Number.isSafeInteger(id) || !['accepted', 'declined', 'expired'].includes(state)) {
    throw new HttpError(400, 'Offer state is invalid.');
  }
  const connection = await pool.getConnection();
  try {
    await connection.beginTransaction();
    const [riders] = await connection.execute(
      'SELECT is_active,kyc_status FROM rider_profile WHERE id = ? FOR UPDATE',
      [req.rider.riderId],
    );
    if (!riders.length || !riders[0].is_active || riders[0].kyc_status === 'suspended') {
      throw new HttpError(403, 'This rider cannot receive or accept job offers.');
    }
    const [rows] = await connection.execute(
      'SELECT * FROM job_offers WHERE id = ? AND rider_profile_id = ? FOR UPDATE',
      [id, req.rider.riderId],
    );
    if (!rows.length || rows[0].state !== 'pending') throw new HttpError(409, 'This offer is no longer pending.');
    const offer = rows[0];
    if (state === 'accepted') {
      if (new Date(offer.expires_at) <= new Date()) throw new HttpError(409, 'This offer has expired.');
      const [counts] = await connection.execute(
        `SELECT COUNT(*) AS count FROM parcels WHERE rider_profile_id = ?
         AND status IN ('assigned','picked_up','out_for_delivery') FOR UPDATE`,
        [req.rider.riderId],
      );
      if (Number(counts[0].count) >= 3) throw new HttpError(409, 'The rider already has the maximum of three active jobs.');
      const parcel = await getParcel(connection, req.rider.riderId, offer.parcel_id, true);
      if (parcel.status !== 'ready_for_pickup') throw new HttpError(409, 'The offered parcel is no longer ready for pickup.');
      await updateStatus(connection, parcel, 'assigned');
    }
    await connection.execute('UPDATE job_offers SET state = ? WHERE id = ?', [state, id]);
    await connection.commit();
    res.json({ id, state });
  } catch (error) {
    await connection.rollback();
    throw error;
  } finally {
    connection.release();
  }
}));

app.patch('/api/parcels/:tracking/status', asyncRoute(async (req, res) => {
  const target = requireString(req.body?.to_status, 'to_status', 32);
  const reason = req.body?.reason == null ? null : requireString(req.body.reason, 'reason', 120);
  if (!Object.hasOwn(transitions, target)) throw new HttpError(400, 'Parcel status is invalid.');
  const connection = await pool.getConnection();
  try {
    await connection.beginTransaction();
    const parcel = await getParcel(connection, req.rider.riderId, req.params.tracking, true);
    let finalTarget = target;
    if (target === 'failed_delivery') {
      if (!['Buyer not home', 'Wrong address', 'Refused'].includes(reason)) {
        throw new HttpError(400, 'A valid delivery failure reason is required.');
      }
      if (reason === 'Refused' || Number(parcel.delivery_attempts) >= 2) {
        finalTarget = 'return_to_sender';
      }
    }
    if (!transitions[parcel.status]?.has(finalTarget)) {
      throw new HttpError(409, `Cannot transition ${parcel.status} to ${finalTarget}.`);
    }
    const updated = await updateStatus(connection, parcel, finalTarget, reason);
    await connection.commit();
    res.json(updated);
  } catch (error) {
    await connection.rollback();
    throw error;
  } finally {
    connection.release();
  }
}));

app.post('/api/parcels/:tracking/delivery', asyncRoute(async (req, res) => {
  const photoPath = requireString(req.body?.photo_path, 'photo_path', 500);
  const otpVerified = req.body?.otp_verified === true;
  const codCollected = numeric(req.body?.cod_collected, 'cod_collected');
  const connection = await pool.getConnection();
  try {
    await connection.beginTransaction();
    const parcel = await getParcel(connection, req.rider.riderId, req.params.tracking, true);
    if (parcel.status !== 'out_for_delivery' || !otpVerified ||
        (Number(parcel.is_prepaid) === 0 && cents(codCollected) !== cents(parcel.cod_amount)) ||
        (Number(parcel.is_prepaid) === 1 && codCollected !== 0)) {
      throw new HttpError(409, 'Delivery proof or exact COD amount is invalid.');
    }
    const now = new Date();
    await connection.execute(
      `UPDATE parcels SET status='delivered',delivered_at=?,delivery_fee=50,
       delivery_otp_verified=TRUE,pod_photo_path=?,cod_collected=?,updated_at=?
       WHERE id=?`,
      [now, photoPath, Number(parcel.is_prepaid) === 0 && Number(parcel.cod_amount) > 0, now, parcel.id],
    );
    await connection.execute(
      'INSERT INTO parcel_status_logs (parcel_id,from_status,to_status,created_at) VALUES (?,?,?,?)',
      [parcel.id, parcel.status, 'delivered', now],
    );
    await connection.commit();
    res.json({ status: 'delivered', tracking_no: parcel.tracking_no });
  } catch (error) {
    await connection.rollback();
    throw error;
  } finally {
    connection.release();
  }
}));

app.post('/api/remittances', asyncRoute(async (req, res) => {
  const amount = numeric(req.body?.amount, 'amount');
  const method = requireString(req.body?.method, 'method', 40);
  const reference = requireString(req.body?.reference, 'reference', 120);
  if (reference.length < 3) throw new HttpError(400, 'Reference must have at least three characters.');
  const connection = await pool.getConnection();
  try {
    await connection.beginTransaction();
    const [pending] = await connection.execute(
      'SELECT COUNT(*) AS count FROM cod_remittances WHERE rider_profile_id=? AND status=? FOR UPDATE',
      [req.rider.riderId, 'pending'],
    );
    if (Number(pending[0].count) > 0) throw new HttpError(409, 'A remittance is already pending confirmation.');
    const [parcels] = await connection.execute(
      `SELECT cod_amount FROM parcels WHERE rider_profile_id=? AND status='delivered'
       AND is_prepaid=FALSE AND cod_collected=TRUE AND cod_remitted=FALSE FOR UPDATE`,
      [req.rider.riderId],
    );
    const expected = parcels.reduce((sum, parcel) => sum + Number(parcel.cod_amount), 0);
    if (cents(amount) !== cents(expected)) throw new HttpError(409, 'Remittance must match the full COD balance.');
    const [result] = await connection.execute(
      `INSERT INTO cod_remittances
       (rider_profile_id,amount,collected,difference,status,reference,method)
       VALUES (?,?,?,0,'pending',?,?)`,
      [req.rider.riderId, expected, amount, reference, method],
    );
    await connection.commit();
    const [rows] = await pool.execute('SELECT * FROM cod_remittances WHERE id=?', [result.insertId]);
    res.status(201).json(rows[0]);
  } catch (error) {
    await connection.rollback();
    throw error;
  } finally {
    connection.release();
  }
}));

app.post('/api/notifications/read-all', asyncRoute(async (req, res) => {
  await pool.execute(
    'UPDATE notifications SET read_at=UTC_TIMESTAMP(3) WHERE rider_profile_id=? AND read_at IS NULL',
    [req.rider.riderId],
  );
  res.json({ ok: true });
}));

app.post('/api/conversations/:id/messages', asyncRoute(async (req, res) => {
  const id = Number(req.params.id);
  const body = requireString(req.body?.body, 'body', 4000);
  const [conversations] = await pool.execute(
    'SELECT id FROM conversations WHERE id=? AND rider_profile_id=?',
    [id, req.rider.riderId],
  );
  if (!conversations.length) throw new HttpError(404, 'Conversation was not found.');
  const connection = await pool.getConnection();
  try {
    await connection.beginTransaction();
    const [result] = await connection.execute(
      'INSERT INTO messages (conversation_id,sender,body) VALUES (?,?,?)',
      [id, 'rider', body],
    );
    await connection.execute('UPDATE conversations SET last_message_at=UTC_TIMESTAMP(3) WHERE id=?', [id]);
    await connection.commit();
    const [rows] = await pool.execute('SELECT * FROM messages WHERE id=?', [result.insertId]);
    res.status(201).json(rows[0]);
  } catch (error) {
    await connection.rollback();
    throw error;
  } finally {
    connection.release();
  }
}));

app.post('/api/emergency-contacts', asyncRoute(async (req, res) => {
  const name = requireString(req.body?.name, 'name', 160);
  const relationship = requireString(req.body?.relationship, 'relationship', 80);
  const phone = requireString(req.body?.phone, 'phone', 32);
  const [contacts] = await pool.execute(
    'SELECT COUNT(*) AS count FROM emergency_contacts WHERE rider_profile_id=?',
    [req.rider.riderId],
  );
  if (Number(contacts[0].count) >= 2) throw new HttpError(409, 'At most two emergency contacts are allowed.');
  const [result] = await pool.execute(
    'INSERT INTO emergency_contacts (rider_profile_id,name,relationship,phone) VALUES (?,?,?,?)',
    [req.rider.riderId, name, relationship, phone],
  );
  const [rows] = await pool.execute('SELECT * FROM emergency_contacts WHERE id=?', [result.insertId]);
  res.status(201).json(rows[0]);
}));

app.put('/api/emergency-contacts/:id', asyncRoute(async (req, res) => {
  const name = requireString(req.body?.name, 'name', 160);
  const relationship = requireString(req.body?.relationship, 'relationship', 80);
  const phone = requireString(req.body?.phone, 'phone', 32);
  const [result] = await pool.execute(
    'UPDATE emergency_contacts SET name=?,relationship=?,phone=? WHERE id=? AND rider_profile_id=?',
    [name, relationship, phone, Number(req.params.id), req.rider.riderId],
  );
  if (!result.affectedRows) throw new HttpError(404, 'Emergency contact was not found.');
  const [rows] = await pool.execute('SELECT * FROM emergency_contacts WHERE id=?', [Number(req.params.id)]);
  res.json(rows[0]);
}));

app.delete('/api/emergency-contacts/:id', asyncRoute(async (req, res) => {
  const [result] = await pool.execute(
    'DELETE FROM emergency_contacts WHERE id=? AND rider_profile_id=?',
    [Number(req.params.id), req.rider.riderId],
  );
  if (!result.affectedRows) throw new HttpError(404, 'Emergency contact was not found.');
  res.status(204).end();
}));

app.post('/api/dev/reset', asyncRoute(async (_req, res) => {
  if (process.env.NODE_ENV !== 'development' || process.env.ALLOW_DEV_RESET !== 'true') {
    throw new HttpError(404, 'Not found.');
  }
  throw new HttpError(501, 'Remote demo reset is unavailable; no data was changed.');
}));

app.use((error, _req, res, _next) => {
  const status = Number(error.status) || 500;
  if (status >= 500) console.error(error);
  res.status(status).json({
    error: status >= 500 ? 'The server could not complete the request.' : error.message,
  });
});

async function main() {
  if (jwtSecret.length < 32) throw new Error('JWT_SECRET must be a random value of at least 32 characters.');
  await pool.query('SELECT 1');
  app.listen(port, host, () => {
    console.log(`Mimoo Rider API listening at http://${host}:${port}`);
  });
}

main().catch((error) => {
  console.error('API startup failed:', error.message);
  process.exitCode = 1;
  pool.end();
});
