import { execSync } from 'child_process'
import { existsSync } from 'fs'
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
    onMessage?.('Installing PHP dependencies...')
    execSync('composer install', { cwd: composerDir, stdio: 'pipe' })
  }
}
