require('dotenv').config();
const bcrypt = require('bcryptjs');
const pool = require('./database');

async function main() {
  const [email, riderUserId] = process.argv.slice(2);
  const password = process.env.RIDER_PASSWORD;
  if (!email || !riderUserId || !password || password.length < 12) {
    throw new Error(
      'Usage: set RIDER_PASSWORD to a 12+ character value, then run npm run rider:create -- <email> <rider-user-id>.',
    );
  }
  const connection = await pool.getConnection();
  try {
    const [profiles] = await connection.execute(
      'SELECT id FROM rider_profile WHERE user_id = ? AND is_active = TRUE',
      [riderUserId],
    );
    if (!profiles.length) throw new Error('Active rider profile was not found.');
    const passwordHash = await bcrypt.hash(password, 12);
    await connection.execute(
      'INSERT INTO rider_accounts (rider_profile_id,email,password_hash) VALUES (?,?,?)',
      [profiles[0].id, email.trim().toLowerCase(), passwordHash],
    );
    console.log(`Login provisioned for ${email.trim().toLowerCase()}.`);
  } finally {
    connection.release();
    await pool.end();
  }
}

main().catch((error) => {
  console.error('Rider provisioning failed:', error.message);
  process.exitCode = 1;
  pool.end();
});
