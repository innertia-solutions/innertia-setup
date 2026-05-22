import { describe, it, expect, vi, beforeEach } from 'vitest'
import path from 'path'

vi.mock('fs', () => ({
  existsSync: vi.fn(),
}))

import { existsSync } from 'fs'
import { resolveComposerDir } from '../post-install.js'

const mockExistsSync = vi.mocked(existsSync)

describe('resolveComposerDir', () => {
  beforeEach(() => {
    mockExistsSync.mockReset()
  })

  it('returns api/ when composer.json exists there', () => {
    mockExistsSync.mockImplementation((p) =>
      p === path.join('/project', 'api', 'composer.json')
    )
    expect(resolveComposerDir('/project')).toBe(path.join('/project', 'api'))
  })

  it('returns backend/ when composer.json exists there (no api/)', () => {
    mockExistsSync.mockImplementation((p) =>
      p === path.join('/project', 'backend', 'composer.json')
    )
    expect(resolveComposerDir('/project')).toBe(path.join('/project', 'backend'))
  })

  it('returns projectDir when composer.json is at root (no api/ or backend/)', () => {
    mockExistsSync.mockImplementation((p) =>
      p === path.join('/project', 'composer.json')
    )
    expect(resolveComposerDir('/project')).toBe('/project')
  })

  it('returns null when no composer.json found', () => {
    mockExistsSync.mockReturnValue(false)
    expect(resolveComposerDir('/project')).toBeNull()
  })

  it('prefers api/ over backend/ when both exist', () => {
    mockExistsSync.mockImplementation((p) =>
      p === path.join('/project', 'api', 'composer.json') ||
      p === path.join('/project', 'backend', 'composer.json')
    )
    expect(resolveComposerDir('/project')).toBe(path.join('/project', 'api'))
  })
})
