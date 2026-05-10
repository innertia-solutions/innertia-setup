import * as p from '@clack/prompts'
import { templates } from './templates.js'

export interface ProjectAnswers {
  projectName: string
  templateId: string
}

export async function runPrompts(): Promise<ProjectAnswers> {
  p.intro('innertia-setup — Innertia project scaffolder')

  const projectName = await p.text({
    message: 'Project name?',
    placeholder: 'my-project',
    validate(value) {
      if (!value) return 'Project name is required'
      if (!/^[a-z0-9-]+$/.test(value))
        return 'Use only lowercase letters, numbers, and hyphens'
    },
  })

  if (p.isCancel(projectName)) {
    p.cancel('Cancelled.')
    process.exit(0)
  }

  const templateId = await p.select({
    message: 'Template?',
    options: templates.map(t => ({
      value: t.id,
      label: t.label,
      hint: t.description,
    })),
  })

  if (p.isCancel(templateId)) {
    p.cancel('Cancelled.')
    process.exit(0)
  }

  return { projectName: projectName as string, templateId: templateId as string }
}
