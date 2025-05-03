-- CreateTable
CREATE TABLE "Usuario" (
    "id" INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    "nombreUsuario" TEXT NOT NULL,
    "contrasena" TEXT NOT NULL,
    "nombre" TEXT NOT NULL,
    "apellidos" TEXT NOT NULL,
    "especialidad" TEXT,
    "numeroRegistro" TEXT,
    "email" TEXT,
    "telefono" TEXT
);

-- CreateTable
CREATE TABLE "Paciente" (
    "id" INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    "nombre" TEXT NOT NULL,
    "apellidos" TEXT NOT NULL,
    "fechaNac" DATETIME,
    "genero" TEXT,
    "telefono" TEXT,
    "email" TEXT,
    "direccion" TEXT,
    "nutricionistaId" INTEGER NOT NULL,
    CONSTRAINT "Paciente_nutricionistaId_fkey" FOREIGN KEY ("nutricionistaId") REFERENCES "Usuario" ("id") ON DELETE RESTRICT ON UPDATE CASCADE
);

-- CreateIndex
CREATE UNIQUE INDEX "Usuario_nombreUsuario_key" ON "Usuario"("nombreUsuario");
