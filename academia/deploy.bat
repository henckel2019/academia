@echo off
echo Empacotando projeto para deploy...
cd /d C:\wamp64\www\academia
del /f C:\Users\Luis\Desktop\condominio.zip 2>nul
powershell -Command "Compress-Archive -Path app,android,database,public,storage,README.md,API.md,.gitignore -DestinationPath C:\Users\Luis\Desktop\academia.zip -Force"
echo Pacote criado em C:\Users\Luis\Desktop\academia.zip
pause
