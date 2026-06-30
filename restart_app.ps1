# Kill existing processes
Write-Host "Killing existing node, dotnet, and VBCSCompiler processes..."
Get-Process -Name "node", "dotnet", "VBCSCompiler" -ErrorAction SilentlyContinue | Stop-Process -Force

# Wait a moment for processes to release locks
Start-Sleep -Seconds 2

# Start Backend
Write-Host "Starting Cozy Comfort API backend..."
Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd backend\CozyComfort.API; dotnet run --launch-profile http"

# Start Frontend
Write-Host "Starting Cozy Comfort React web client..."
Start-Process powershell -ArgumentList "-NoExit", "-Command", "npm run dev"

Write-Host "Cozy Comfort Application services launched successfully."

