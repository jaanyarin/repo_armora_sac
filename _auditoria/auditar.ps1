<#
.SYNOPSIS
  Auditor automatico ARMORA NextGen - Capa mecanica (Layer 1).
  Ejecuta build, lint y tests del frontend + backend.

.DESCRIPTION
  Este script corre los chequeos automatizables definidos en los gates
  G-DEVOPS, G-TEST y G-TS del Senior Code & Architecture Quality Auditor.

  USO (arquitecto):
    .\_auditoria\auditar.ps1            # corre todo
    .\_auditoria\auditar.ps1 -Frontend  # solo frontend
    .\_auditoria\auditar.ps1 -Backend   # solo backend
    .\_auditoria\auditar.ps1 -Quick     # solo build + lint, sin tests

  SALIDA:
    0 -> todos los chequeos pasan
    1 -> uno o mas chequeos fallan

  OUTPUT:
    Los resultados detallados se escriben a _auditoria/auditar-resultado.json
    para consumo por el agente auditor (Layer 2).
#>

param(
  [switch]$Frontend,
  [switch]$Backend,
  [switch]$Quick
)

$ErrorActionPreference = "Continue"
$repoRoot = Resolve-Path "$PSScriptRoot\.."
$resultado = @{
  timestamp = (Get-Date -Format "yyyy-MM-ddTHH:mm:ss")
  passed = $true
  frontend = @{}
  backend = @{}
  gates = @{}
}

# Colores
$green = "Green"
$red = "Red"
$yellow = "Yellow"

function Write-Step($text) { Write-Host "`n==> $text" -ForegroundColor Cyan }
function Write-Pass($text) { Write-Host "  [PASS] $text" -ForegroundColor $green }
function Write-Fail($text) { Write-Host "  [FAIL] $text" -ForegroundColor $red; $global:anyFail = $true }

$global:anyFail = $false

# --- Frontend ---
if (-not $Backend) {
  Write-Step "FRONTEND: npm run build (tsc -b + vite build)"
  Push-Location "$repoRoot\frontend"
  $buildOut = npm run build 2>&1
  $buildExit = $LASTEXITCODE
  if ($buildExit -eq 0) {
    Write-Pass "build exitoso"
    $resultado.frontend.build = "pass"
    $resultado.gates["G-DEVOPS-build"] = "pass"
  } else {
    Write-Fail "build fallo (exit code $buildExit)"
    $resultado.frontend.build = "fail"
    $resultado.gates["G-DEVOPS-build"] = "fail"
    $resultado.frontend.build_log = ($buildOut | Out-String)
  }

  Write-Step "FRONTEND: npm run lint"
  $lintOut = npm run lint 2>&1
  $lintExit = $LASTEXITCODE
  if ($lintExit -eq 0) {
    Write-Pass "lint exitoso"
    $resultado.frontend.lint = "pass"
    $resultado.gates["G-DEVOPS-lint"] = "pass"
  } else {
    Write-Fail "lint encontro errores (exit code $lintExit)"
    $resultado.frontend.lint = "fail"
    $resultado.gates["G-DEVOPS-lint"] = "fail"
    $resultado.frontend.lint_log = ($lintOut | Out-String)
  }

  if (-not $Quick) {
    Write-Step "FRONTEND: npm run test"
    $testOut = npm run test 2>&1
    $testExit = $LASTEXITCODE
    if ($testExit -eq 0) {
      Write-Pass "tests unitarios exitosos"
      $resultado.frontend.test = "pass"
      $resultado.gates["G-TEST-fe"] = "pass"
    } else {
      Write-Fail "tests frontend fallaron (exit code $testExit)"
      $resultado.frontend.test = "fail"
      $resultado.gates["G-TEST-fe"] = "fail"
      $resultado.frontend.test_log = ($testOut | Out-String)
    }
  }

  Pop-Location
}

# --- Backend ---
if ((-not $Frontend) -and (-not $Quick)) {
  Write-Step "BACKEND: composer test (PHPUnit)"
  Push-Location "$repoRoot\backend"
  $testOut = composer test 2>&1
  $testExit = $LASTEXITCODE
  if ($testExit -eq 0) {
    Write-Pass "tests backend exitosos"
    $resultado.backend.test = "pass"
    $resultado.gates["G-TEST-be"] = "pass"
  } else {
    Write-Fail "tests backend fallaron (exit code $testExit)"
    $resultado.backend.test = "fail"
    $resultado.gates["G-TEST-be"] = "fail"
    $resultado.backend.test_log = ($testOut | Out-String)
  }
  Pop-Location
}

# --- Reporte final ---
$resultado.passed = -not $global:anyFail
$resultado | ConvertTo-Json -Depth 4 | Out-File "$PSScriptRoot\auditar-resultado.json" -Encoding utf8

Write-Step "RESULTADO FINAL"
if ($resultado.passed) {
  Write-Host "  [OK] TODOS LOS CHEQUEOS PASARON" -ForegroundColor $green
} else {
  Write-Host "  [FAIL] HUBO FALLOS - revisa los logs arriba" -ForegroundColor $red
  Write-Host "  [INFO] Resultado detallado: _auditoria\auditar-resultado.json" -ForegroundColor $yellow
}

if ($global:anyFail) { exit 1 } else { exit 0 }
