require('dotenv').config();
const pool = require('./database');

async function main() {
  const connection = await pool.getConnection();
  try {
    await connection.beginTransaction();
    const [existing] = await connection.query(
      'SELECT id FROM rider_profile WHERE user_id = ?',
      ['RDR-2026-0842'],
    );
    if (existing.length) {
      console.log('Demo rider already exists; seed was not applied again.');
      await connection.rollback();
      return;
    }

    const now = new Date();
    const [profileResult] = await connection.execute(
      `INSERT INTO rider_profile
        (user_id,name,phone,address,vehicle_type,plate_no,license_no,
         license_expires_on,or_cr_expires_on,kyc_status,area_id,area_name,
         fleet_name,hub_name)
       VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)`,
      [
        'RDR-2026-0842', 'Pedro Jose Garcia', '+639171234567',
        'Poblacion I, Santa Cruz, Laguna', 'Motorcycle', '123ABC',
        'N01-23-456789', '2027-10-30', '2027-10-30', 'verified',
        'poblacion-i', 'Poblacion I', 'Bilis Hatid Express Inc.',
        'Santa Cruz, Laguna Hub',
      ],
    );
    const riderId = profileResult.insertId;
    const parcels = [
      ['TRK-0012345','delivery','out_for_delivery','Santa Cruz Handicrafts','Santa Cruz Public Market, Poblacion I','Juan Miguel D.','+639181234567','Poblacion I, Santa Cruz, Laguna','poblacion-i','Poblacion I',500,0,0,0,0,0,0,now,'Handwoven table runner'],
      ['TRK-0012450','pickup','ready_for_pickup','Santa Cruz Handicrafts','Santa Cruz Public Market, Poblacion I','Liza Ramos','+639181234568','Poblacion I, Santa Cruz, Laguna','poblacion-i','Poblacion I',0,1,0,0,0,0,0,now,'Local goods parcel'],
      ['TRK-0012390','delivery','assigned','Santa Cruz Local Goods','Santa Cruz Public Market, Bubukal','Liza Ramos','+639181234569','Bubukal, Santa Cruz, Laguna','bubukal','Bubukal',650,0,0,0,0,0,0,now,'Local goods parcel'],
      ['TRK-0012388','pickup','picked_up','Santa Cruz Handicrafts','Santa Cruz Public Market, Palasan','Juan Miguel D.','+639181234570','Palasan, Santa Cruz, Laguna','palasan','Palasan',0,1,0,30,0,0,0,now,'Local goods parcel'],
      ['TRK-0012301','delivery','failed_delivery','Santa Cruz Local Goods','Santa Cruz Public Market, Gatid','Nena Cruz','+639181234571','Gatid, Santa Cruz, Laguna','gatid','Gatid',650,0,0,0,0,0,1,now,'Local goods parcel'],
      ['TRK-0012322','delivery','delivered','Santa Cruz Local Goods','Santa Cruz Public Market, Duhat','Mario Santos','+639181234572','Duhat, Santa Cruz, Laguna','duhat','Duhat',650,0,1,0,50,1,0,now,'Local goods parcel'],
      ['TRK-0012308','delivery','delivered','Santa Cruz Local Goods','Santa Cruz Public Market, Buenavista','Carla Reyes','+639181234573','Buenavista, Santa Cruz, Laguna','buenavista','Buenavista',650,0,1,0,50,1,0,now,'Local goods parcel'],
    ];

    for (const p of parcels) {
      const [result] = await connection.execute(
        `INSERT INTO parcels
          (rider_profile_id,tracking_no,job_type,status,seller_name,seller_address,
           buyer_name,buyer_phone,address,area_id,area_name,cod_amount,is_prepaid,
           cod_collected,pickup_fee,delivery_fee,delivery_otp_verified,
           delivery_attempts,updated_at,assigned_at,picked_up_at,out_for_delivery_at,
           delivered_at,route_code,landmark,carrier,item,weight_kg,received_at,sorted_at)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)`,
        [
          riderId, ...p.slice(0, 17), now,
          p[2] === 'assigned' ? now : null,
          ['picked_up','at_hub','out_for_delivery','delivered','failed_delivery'].includes(p[2]) ? now : null,
          ['out_for_delivery','delivered','failed_delivery'].includes(p[2]) ? now : null,
          p[2] === 'delivered' ? now : null,
          `SC-${p[9].slice(0,1).toUpperCase()}01`,
          'Near Santa Cruz Public Market', 'jnt', p[18], 1.5,
          new Date(now.getTime() - 7200000), new Date(now.getTime() - 3600000),
        ],
      );
      await connection.execute(
        'INSERT INTO parcel_status_logs (parcel_id,from_status,to_status,created_at) VALUES (?,?,?,?)',
        [result.insertId, null, p[2], now],
      );
    }

    await connection.execute(
      `INSERT INTO job_offers (rider_profile_id,parcel_id,area_name,payout,cod_amount,km,expires_at)
       VALUES (?,?,?,?,?,?,?)`,
      [riderId, 'TRK-0012450', 'Poblacion I', 30, 0, 2.4, new Date(now.getTime() + 60000)],
    );
    await connection.execute(
      `INSERT INTO notifications (rider_profile_id,title,body,kind,parcel_id,created_at)
       VALUES (?,?,?,?,?,?),(?,?,?,?,?,?),(?,?,?,?,?,?)`,
      [
        riderId,'New job offer near you','Poblacion I · ₱30 pickup pay · Expires in 60s','job_offer','TRK-0012450',now,
        riderId,'Parcel assigned to you','TRK-0012390 is at Santa Cruz Hub.','parcel_assigned','TRK-0012390',now,
        riderId,'Seller marked parcels ready','Santa Cruz Handicrafts has 2 parcels ready.','seller_ready',null,now,
      ],
    );
    const titles = ['Juan Miguel D.','Santa Cruz Hub Dispatch','Santa Cruz Handicrafts'];
    const [conversations] = await connection.execute(
      'INSERT INTO conversations (rider_profile_id,title,last_message_at) VALUES (?,?,?),(?,?,?),(?,?,?)',
      titles.flatMap((title) => [riderId, title, now]),
    );
    const [conversationRows] = await connection.execute(
      'SELECT id,title FROM conversations WHERE rider_profile_id = ? ORDER BY id DESC LIMIT 3',
      [riderId],
    );
    const messageBodies = [
      "Yes po, I'm waiting at the blue gate now.",
      'Please return uncollected parcels before 7:00 PM.',
      'The parcels are sealed and ready on the counter.',
    ];
    for (const [index, conversation] of conversationRows.reverse().entries()) {
      await connection.execute(
        'INSERT INTO messages (conversation_id,sender,body,created_at) VALUES (?,?,?,?)',
        [conversation.id, 'hub', messageBodies[index], now],
      );
    }
    await connection.execute(
      'INSERT INTO emergency_contacts (rider_profile_id,name,relationship,phone) VALUES (?,?,?,?),(?,?,?,?)',
      [riderId,'Ana Dela Cruz','Coordinator','+639181234567',riderId,'Maria Garcia','Family','+639191234567'],
    );
    await connection.commit();
    console.log(`Seeded demo profile ${riderId}. Create its login with npm run rider:create.`);
  } catch (error) {
    await connection.rollback();
    throw error;
  } finally {
    connection.release();
  }
}

main().catch((error) => {
  console.error('Demo seed failed:', error.message);
  process.exitCode = 1;
}).finally(() => pool.end());
