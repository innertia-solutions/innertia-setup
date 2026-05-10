import { describe, it, expect } from 'vitest'
import { substituteVariables, buildVars } from '../scaffold.js'

describe('substituteVariables', () => {
  it('replaces a single placeholder', () => {
    const result = substituteVariables('Hello {{PROJECT_NAME}}!', {
      PROJECT_NAME: 'pomely',
    })
    expect(result).toBe('Hello pomely!')
  })

  it('replaces multiple occurrences', () => {
    const result = substituteVariables(
      'DB_DATABASE={{PROJECT_NAME}}\nDB_PASSWORD={{PROJECT_NAME}}',
      { PROJECT_NAME: 'pomely' }
    )
    expect(result).toBe('DB_DATABASE=pomely\nDB_PASSWORD=pomely')
  })

  it('replaces all defined placeholders', () => {
    const result = substituteVariables(
      '{{PROJECT_NAME}} {{PROJECT_NAME_UPPER}}',
      { PROJECT_NAME: 'pomely', PROJECT_NAME_UPPER: 'POMELY' }
    )
    expect(result).toBe('pomely POMELY')
  })

  it('leaves unknown placeholders untouched', () => {
    const result = substituteVariables('{{UNKNOWN}}', { PROJECT_NAME: 'pomely' })
    expect(result).toBe('{{UNKNOWN}}')
  })
})

describe('buildVars', () => {
  it('derives PROJECT_NAME_UPPER from project name', () => {
    const vars = buildVars('pomely')
    expect(vars.PROJECT_NAME).toBe('pomely')
    expect(vars.PROJECT_NAME_UPPER).toBe('POMELY')
  })

  it('sets DB_PASSWORD equal to project name', () => {
    const vars = buildVars('pomely')
    expect(vars.DB_PASSWORD).toBe('pomely')
  })

  it('uses fixed port values', () => {
    const vars = buildVars('any-project')
    expect(vars.APP_PORT).toBe('8100')
    expect(vars.DB_PORT).toBe('5437')
    expect(vars.REDIS_PORT).toBe('63791')
    expect(vars.FRONTEND_PORT).toBe('3000')
  })
})
