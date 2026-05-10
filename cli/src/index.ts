#!/usr/bin/env node
import * as p from '@clack/prompts'
import path from 'path'
import { fileURLToPath } from 'url'
import { runPrompts } from './prompts.js'

const __dirname = path.dirname(fileURLToPath(import.meta.url))

async function main() {
  const { projectName, templateId } = await runPrompts()

  const spinner = p.spinner()
  spinner.start('Creating project...')

  const { createProject } = await import(
    path.resolve(__dirname, '../../scripts/create.js')
  )
  await createProject(projectName, templateId)

  spinner.stop('Done.')

  p.outro(
    `Project ready at ./${projectName}\n` +
    `Next: cd ${projectName} && docker compose up`
  )
}

main().catch(err => {
  console.error(err)
  process.exit(1)
})
