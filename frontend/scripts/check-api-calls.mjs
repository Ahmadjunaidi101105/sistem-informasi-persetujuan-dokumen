#!/usr/bin/env node
/**
 * Fails the build when a component calls an API method that does not exist.
 *
 * This class of defect shipped three times in this project — projectsApi.get(),
 * exportsApi.exportPdf()/exportExcel() and the whole set of reviewsApi review
 * actions were all invoked but never defined. Nothing caught them: the call
 * only throws when a user clicks the button, so the build stayed green and the
 * feature silently did nothing.
 *
 * Run with: npm run check:api
 */

import { readdirSync, readFileSync, statSync } from 'node:fs'
import { join, relative, basename } from 'node:path'
import { fileURLToPath } from 'node:url'

const SRC = join(fileURLToPath(new URL('.', import.meta.url)), '..', 'src')
const API_DIR = join(SRC, 'api')

/** Recursively collect files with the given extensions. */
function walk(dir, exts, found = []) {
  for (const entry of readdirSync(dir)) {
    const full = join(dir, entry)
    if (statSync(full).isDirectory()) {
      walk(full, exts, found)
    } else if (exts.some((e) => entry.endsWith(e))) {
      found.push(full)
    }
  }
  return found
}

// 1. What each api/*.js module actually exports.
const defined = new Map()
for (const file of walk(API_DIR, ['.js'])) {
  const src = readFileSync(file, 'utf8')
  const objMatch = src.match(/export const (\w+)\s*=\s*\{([\s\S]*?)\n\}/)
  if (!objMatch) continue
  const [, objName, body] = objMatch
  const methods = new Set([...body.matchAll(/^\s{2}(\w+)\s*:/gm)].map((m) => m[1]))
  defined.set(objName, { file: basename(file), methods })
}

// 2. Every xxxApi.method() call outside the api directory.
const problems = []
for (const file of walk(SRC, ['.vue', '.js'])) {
  if (file.startsWith(API_DIR)) continue
  const src = readFileSync(file, 'utf8')
  for (const match of src.matchAll(/\b(\w+Api)\.(\w+)\s*\(/g)) {
    const [, obj, method] = match
    const mod = defined.get(obj)
    if (!mod) {
      problems.push(`${relative(SRC, file)}: unknown API module "${obj}" (calling .${method}())`)
    } else if (!mod.methods.has(method)) {
      problems.push(
        `${relative(SRC, file)}: ${obj}.${method}() is not defined in api/${mod.file}`
      )
    }
  }
}

if (problems.length > 0) {
  console.error('API call check failed:\n')
  for (const p of problems) console.error(`  ✗ ${p}`)
  console.error(`\n${problems.length} problem(s) found.`)
  process.exit(1)
}

console.log(`API call check passed (${defined.size} modules).`)
