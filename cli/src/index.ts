#!/usr/bin/env node
import * as p from '@clack/prompts'
import { runPrompts } from './prompts.js'
import { createProject } from '../../scripts/create.js'

async function main() {
  const { projectName, templateId } = await runPrompts()

  const spinner = p.spinner()
  spinner.start('Scaffolding project...')

  await createProject(projectName, templateId, (msg) => spinner.message(msg))

  spinner.stop('Done.')

  const nextSteps = projectName === '.'
    ? 'docker compose up'
    : `cd ${projectName} && docker compose up`

  p.outro(`Project ready!\nNext: ${nextSteps}`)
}

main().catch(err => {
  console.error(err)
  process.exit(1)
})
