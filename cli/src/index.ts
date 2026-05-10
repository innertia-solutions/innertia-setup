#!/usr/bin/env node
import * as p from '@clack/prompts'
import { runPrompts } from './prompts.js'
import { createProject } from '../../scripts/create.js'

async function main() {
  const { projectName, templateId } = await runPrompts()

  const spinner = p.spinner()
  spinner.start('Creating project...')

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
