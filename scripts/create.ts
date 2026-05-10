#!/usr/bin/env node
import path from 'path'
import { fileURLToPath } from 'url'
import { scaffoldProject, buildVars } from './scaffold.js'
import { runPostInstall } from './post-install.js'

const __dirname = path.dirname(fileURLToPath(import.meta.url))

export async function createProject(projectName: string, templateId: string): Promise<void> {
  const templateDir = path.resolve(__dirname, '../templates', templateId)
  const targetDir = projectName === '.'
    ? process.cwd()
    : path.resolve(process.cwd(), projectName)

  const vars = buildVars(projectName)
  await scaffoldProject(templateDir, targetDir, vars)
  runPostInstall(targetDir)
}

// Allow running directly: npx tsx scripts/create.ts my-project app
if (process.argv[1] === fileURLToPath(import.meta.url)) {
  const [, , projectName, templateId] = process.argv
  if (!projectName || !templateId) {
    console.error('Usage: tsx scripts/create.ts <project-name> <template-id>')
    process.exit(1)
  }
  createProject(projectName, templateId).catch(err => {
    console.error(err)
    process.exit(1)
  })
}
