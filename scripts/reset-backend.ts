#!/usr/bin/env node
/**
 * Reset (or initialize) only the backend of an existing project.
 *
 * Usage:
 *   npx tsx scripts/reset-backend.ts <project-name> [template] [target-dir]
 *
 * Examples:
 *   npx tsx scripts/reset-backend.ts documentia saas
 *   npx tsx scripts/reset-backend.ts documentia saas ./documentia/backend
 *
 * template defaults to "saas". target-dir defaults to ./<project-name>/backend
 */
import path from 'path'
import { fileURLToPath } from 'url'
import { promises as fs } from 'fs'
import { scaffoldProject, buildVars } from './scaffold.js'

const __dirname = path.dirname(fileURLToPath(import.meta.url))

async function resetBackend(projectName: string, template = 'saas', targetDir?: string): Promise<void> {
  const templateDir = path.resolve(__dirname, '../templates', template, 'backend')
  const resolvedTarget = targetDir
    ? path.resolve(process.cwd(), targetDir)
    : path.resolve(process.cwd(), projectName, 'backend')

  try {
    await fs.access(templateDir)
  } catch {
    console.error(`Template not found: ${templateDir}`)
    process.exit(1)
  }

  console.log(`Scaffolding backend from template "${template}"...`)
  console.log(`  source : ${templateDir}`)
  console.log(`  target : ${resolvedTarget}`)

  const vars = buildVars(projectName)
  await scaffoldProject(templateDir, resolvedTarget, vars)

  console.log('Done. Run composer install + migrate:fresh inside the container.')
}

const [, , projectName, template, targetDir] = process.argv
if (!projectName) {
  console.error('Usage: tsx scripts/reset-backend.ts <project-name> [template] [target-dir]')
  process.exit(1)
}

resetBackend(projectName, template ?? 'saas', targetDir).catch(err => {
  console.error(err)
  process.exit(1)
})
