const { app, BrowserWindow, ipcMain } = require('electron');
const path = require('path');
const { PrismaClient } = require('@prisma/client');

const prisma = new PrismaClient();
let mainWindow;

function createWindow() {
  mainWindow = new BrowserWindow({
    width: 1000,
    height: 700,
    webPreferences: {
      nodeIntegration: true,
      contextIsolation: false,
      preload: path.join(__dirname, 'preload.js')
    }
  });

  mainWindow.loadFile(path.join(__dirname, 'public/index.html'));
}

app.whenReady().then(() => {
  createWindow();
  
  // Configurar eventos IPC para la comunicación con la base de datos
  ipcMain.handle('get-pacientes', async () => {
    try {
      const pacientes = await prisma.paciente.findMany({
        select: {
          id: true,
          nombre: true,
          apellidos: true,
          telefono: true,
          email: true
        }
      });
      return pacientes;
    } catch (error) {
      console.error('Error al obtener pacientes:', error);
      throw error;
    }
  });
});

app.on('window-all-closed', () => {
  if (process.platform !== 'darwin') {
    app.quit();
  }
});

app.on('activate', () => {
  if (BrowserWindow.getAllWindows().length === 0) {
    createWindow();
  }
});

app.on('before-quit', async () => {
  await prisma.$disconnect();
});