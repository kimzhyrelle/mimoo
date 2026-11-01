require('dotenv').config();
const fs = require('node:fs/promises');
const path = require('node:path');
const mysql = require('mysql2/promise');

async function main() {
  const connection = await mysql.createConnection({
    host: process.env.MYSQL_HOST || '127.0.0.1',
    port: Number(process.env.MYSQL_PORT || 3306),
    user: process.env.MYSQL_USER || 'root',
    password: process.env.MYSQL_PASSWORD || '',
    multipleStatements: true,
  });
  try {
    const schema = await fs.readFile(path.join(__dirname, '..', 'schema.sql'), 'utf8');
    await connection.query(schema);
    console.log('MySQL schema is ready.');
  } finally {
    await connection.end();
  }
}

main().catch((error) => {
  console.error('Schema migration failed:', error.message);
  process.exitCode = 1;
});
