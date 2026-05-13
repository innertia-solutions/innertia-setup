import { execSync } from 'child_process'
import { existsSync, copyFileSync } from 'fs'
import path from 'path'

export function runPostInstall(projectDir: string, onMessage?: (msg: string) => void): void {
  // Git init
  onMessage?.('Initializing git repository...')
  execSync('git init', { cwd: projectDir, stdio: 'pipe' })
  execSync('git add -A', { cwd: projectDir, stdio: 'pipe' })
  execSync(
    'git commit -m "chore: initial project scaffold (innertia-setup)"',
    { cwd: projectDir, stdio: 'pipe', env: { ...process.env, GIT_CONFIG_COUNT: '1', GIT_CONFIG_KEY_0: 'commit.gpgsign', GIT_CONFIG_VALUE_0: 'false' } }
  )

  // Composer install — detect backend dir or root-level composer.json
  const backendDir = path.join(projectDir, 'backend')
  const composerDir = existsSync(path.join(backendDir, 'composer.json'))
    ? backendDir
    : existsSync(path.join(projectDir, 'composer.json'))
      ? projectDir
      : null

  if (composerDir) {
    // Copy .env.example → .env before composer install so that
    // the post-autoload-dump hook (php artisan package:discover) can bootstrap Laravel.
    const envExample = path.join(composerDir, '.env.example')
    const envFile    = path.join(composerDir, '.env')
    if (existsSync(envExample) && !existsSync(envFile)) {
      copyFileSync(envExample, envFile)
    }

    onMessage?.('Installing PHP dependencies...')
    // --no-scripts skips post-autoload-dump hooks (php artisan package:discover)
    // which require a running DB/app config that doesn't exist yet during scaffolding.
    execSync('composer install --no-scripts', { cwd: composerDir, stdio: 'pipe' })

    // Generate app key so artisan can bootstrap for subsequent commands.
    onMessage?.('Generating application key...')
    execSync('php artisan key:generate --ansi', { cwd: composerDir, stdio: 'pipe' })

    // Publica migraciones en database/migrations/ para inspeccionarlas o personalizarlas.
    onMessage?.('Publishing migrations...')
    execSync(
      'php artisan vendor:publish --tag=innertia-migrations --no-interaction',
      { cwd: composerDir, stdio: 'pipe' }
    )

    // Publica el stub de rutas como routes/api.php del proyecto.
    onMessage?.('Publishing routes...')
    execSync(
      'php artisan vendor:publish --tag=innertia-routes --no-interaction',
      { cwd: composerDir, stdio: 'pipe' }
    )
  }
}
