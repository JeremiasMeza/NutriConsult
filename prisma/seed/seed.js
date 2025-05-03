// prisma/seed.js
const { PrismaClient } = require('@prisma/client');
const bcrypt = require('bcryptjs');

const prisma = new PrismaClient();

async function main() {
  // Crear usuario administrador
  const adminPassword = await bcrypt.hash('admin', 10);
  const admin = await prisma.usuario.upsert({
    where: { nombreUsuario: 'admin' },
    update: {},
    create: {
      nombreUsuario: 'admin',
      contrasena: adminPassword,
      nombre: 'Administrador',
      apellidos: 'Sistema',
      email: 'admin@nutriconsult.local',
    },
  });
  
  console.log('Usuario administrador creado:', admin.nombreUsuario);
  
  // Crear pacientes de ejemplo
  const paciente1 = await prisma.paciente.create({
    data: {
      nombre: 'Ana',
      apellidos: 'García López',
      fechaNac: new Date('1990-05-15'),
      genero: 'F',
      telefono: '612345678',
      email: 'ana.garcia@ejemplo.com',
      direccion: 'Calle Principal 123',
      nutricionistaId: admin.id,
    },
  });
  
  console.log('Paciente creado:', `${paciente1.nombre} ${paciente1.apellidos}`);
}

main()
  .catch((e) => {
    console.error(e);
    process.exit(1);
  })
  .finally(async () => {
    await prisma.$disconnect();
  });