import { promises as fs } from 'fs'
import path from 'path'
import fse from 'fs-extra'
import glob from 'fast-glob'

export type Vars = Record<string, string>

const BINARY_EXTENSIONS = new Set([
  '.png', '.jpg', '.jpeg', '.gif', '.ico',
  '.woff', '.woff2', '.ttf', '.eot', '.pdf', '.zip',
])

function isBinary(filePath: string): boolean {
  return BINARY_EXTENSIONS.has(path.extname(filePath).toLowerCase())
}

export function substituteVariables(content: string, vars: Vars): string {
  return content.replace(/\{\{([A-Z_]+)\}\}/g, (match, key) => {
    return key in vars ? vars[key] : match
  })
}

export function buildVars(projectName: string): Vars {
  return {
    PROJECT_NAME: projectName,
    PROJECT_NAME_UPPER: projectName.toUpperCase(),
    DB_PASSWORD: projectName,
    APP_PORT: '8100',
    DB_PORT: '5437',
    REDIS_PORT: '63791',
    FRONTEND_PORT: '3000',
    XDEBUG_PORT: '9003',
  }
}

export function generateEnvContent(exampleContent: string): string {
  return exampleContent
}

export async function scaffoldProject(
  templateDir: string,
  targetDir: string,
  vars: Vars
): Promise<void> {
  await fse.copy(templateDir, targetDir)

  const files = await glob('**/*', {
    cwd: targetDir,
    dot: true,
    onlyFiles: true,
  })

  for (const file of files) {
    const filePath = path.join(targetDir, file)
    if (isBinary(filePath)) continue

    const content = await fs.readFile(filePath, 'utf-8')
    const substituted = substituteVariables(content, vars)
    await fs.writeFile(filePath, substituted, 'utf-8')
  }

  const envExamples = files.filter(f => path.basename(f) === '.env.example')
  for (const example of envExamples) {
    const examplePath = path.join(targetDir, example)
    const envPath = path.join(path.dirname(examplePath), '.env')
    const content = await fs.readFile(examplePath, 'utf-8')
    await fs.writeFile(envPath, generateEnvContent(content), 'utf-8')
  }
}
