import { execSync } from 'child_process'

export function runPostInstall(projectDir: string): void {
  execSync('git init', { cwd: projectDir, stdio: 'pipe' })
  execSync('git add -A', { cwd: projectDir, stdio: 'pipe' })
  execSync(
    'git commit -m "chore: initial project scaffold (innertia-setup)"',
    { cwd: projectDir, stdio: 'pipe', env: { ...process.env, GIT_CONFIG_COUNT: '1', GIT_CONFIG_KEY_0: 'commit.gpgsign', GIT_CONFIG_VALUE_0: 'false' } }
  )
}
