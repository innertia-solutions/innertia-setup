#!/usr/bin/env node

// cli/src/index.ts
import * as p2 from "@clack/prompts";

// cli/src/prompts.ts
import * as p from "@clack/prompts";

// cli/src/templates.ts
var templates = [
  {
    id: "nuxt-landing",
    label: "Nuxt Landing",
    description: "Standalone Nuxt site for a landing page"
  },
  {
    id: "laravel-api",
    label: "Laravel API",
    description: "Standalone Laravel REST API with Docker"
  },
  {
    id: "app",
    label: "App (Laravel + Nuxt)",
    description: "Monorepo with Laravel backend + Nuxt frontend + Docker Compose"
  },
  {
    id: "saas",
    label: "SaaS (Laravel + Nuxt + Multitenancy)",
    description: "Monorepo with multitenancy, Laravel + Nuxt + Docker Compose"
  }
];

// cli/src/prompts.ts
async function runPrompts() {
  p.intro("innertia-setup \u2014 Innertia project scaffolder");
  const projectName = await p.text({
    message: "Project name? (use . to install in current directory)",
    placeholder: "my-project",
    validate(value) {
      if (!value) return "Project name is required";
      if (value !== "." && !/^[a-z0-9-]+$/.test(value))
        return "Use only lowercase letters, numbers, and hyphens (or . for current directory)";
    }
  });
  if (p.isCancel(projectName)) {
    p.cancel("Cancelled.");
    process.exit(0);
  }
  const templateId = await p.select({
    message: "Template?",
    options: templates.map((t) => ({
      value: t.id,
      label: t.label,
      hint: t.description
    }))
  });
  if (p.isCancel(templateId)) {
    p.cancel("Cancelled.");
    process.exit(0);
  }
  return { projectName, templateId };
}

// scripts/create.ts
import path3 from "path";
import { fileURLToPath } from "url";

// scripts/scaffold.ts
import { promises as fs } from "fs";
import path from "path";
import fse from "fs-extra";
import glob from "fast-glob";
var BINARY_EXTENSIONS = /* @__PURE__ */ new Set([
  ".png",
  ".jpg",
  ".jpeg",
  ".gif",
  ".ico",
  ".woff",
  ".woff2",
  ".ttf",
  ".eot",
  ".pdf",
  ".zip"
]);
function isBinary(filePath) {
  return BINARY_EXTENSIONS.has(path.extname(filePath).toLowerCase());
}
function substituteVariables(content, vars) {
  return content.replace(/\{\{([A-Z_]+)\}\}/g, (match, key) => {
    return key in vars ? vars[key] : match;
  });
}
function buildVars(projectName) {
  return {
    PROJECT_NAME: projectName,
    PROJECT_NAME_UPPER: projectName.toUpperCase(),
    DB_PASSWORD: projectName,
    APP_PORT: "8100",
    DB_PORT: "5437",
    REDIS_PORT: "63791",
    FRONTEND_PORT: "3000",
    XDEBUG_PORT: "9003"
  };
}
function generateEnvContent(exampleContent) {
  return exampleContent;
}
async function scaffoldProject(templateDir, targetDir, vars) {
  await fse.copy(templateDir, targetDir, {
    filter: (src) => path.basename(src) !== ".npmignore"
  });
  const files = await glob("**/*", {
    cwd: targetDir,
    dot: true,
    onlyFiles: true,
    ignore: [".git/**"]
  });
  for (const file of files) {
    const filePath = path.join(targetDir, file);
    if (isBinary(filePath)) continue;
    const content = await fs.readFile(filePath, "utf-8");
    const substituted = substituteVariables(content, vars);
    await fs.writeFile(filePath, substituted, "utf-8");
  }
  const envExamples = files.filter((f) => path.basename(f) === ".env.example");
  for (const example of envExamples) {
    const examplePath = path.join(targetDir, example);
    const envPath = path.join(path.dirname(examplePath), ".env");
    const content = await fs.readFile(examplePath, "utf-8");
    await fs.writeFile(envPath, generateEnvContent(content), "utf-8");
  }
}

// scripts/post-install.ts
import { execSync } from "child_process";
import { existsSync } from "fs";
import path2 from "path";
function runPostInstall(projectDir, onMessage) {
  onMessage?.("Initializing git repository...");
  execSync("git init", { cwd: projectDir, stdio: "pipe" });
  execSync("git add -A", { cwd: projectDir, stdio: "pipe" });
  execSync(
    'git commit -m "chore: initial project scaffold (innertia-setup)"',
    { cwd: projectDir, stdio: "pipe", env: { ...process.env, GIT_CONFIG_COUNT: "1", GIT_CONFIG_KEY_0: "commit.gpgsign", GIT_CONFIG_VALUE_0: "false" } }
  );
  const backendDir = path2.join(projectDir, "backend");
  const composerDir = existsSync(path2.join(backendDir, "composer.json")) ? backendDir : existsSync(path2.join(projectDir, "composer.json")) ? projectDir : null;
  if (composerDir) {
    onMessage?.("Installing PHP dependencies...");
    execSync("composer install", { cwd: composerDir, stdio: "pipe" });
  }
}

// scripts/create.ts
var __dirname = path3.dirname(fileURLToPath(import.meta.url));
async function createProject(projectName, templateId, onMessage) {
  const templateDir = path3.resolve(__dirname, "../templates", templateId);
  const targetDir = projectName === "." ? process.cwd() : path3.resolve(process.cwd(), projectName);
  const vars = buildVars(projectName);
  await scaffoldProject(templateDir, targetDir, vars);
  runPostInstall(targetDir, onMessage);
}
if (process.argv[1] === fileURLToPath(import.meta.url)) {
  const [, , projectName, templateId] = process.argv;
  if (!projectName || !templateId) {
    console.error("Usage: tsx scripts/create.ts <project-name> <template-id>");
    process.exit(1);
  }
  createProject(projectName, templateId).catch((err) => {
    console.error(err);
    process.exit(1);
  });
}

// cli/src/index.ts
async function main() {
  const { projectName, templateId } = await runPrompts();
  const spinner2 = p2.spinner();
  spinner2.start("Scaffolding project...");
  await createProject(projectName, templateId, (msg) => spinner2.message(msg));
  spinner2.stop("Done.");
  const nextSteps = projectName === "." ? "docker compose up" : `cd ${projectName} && docker compose up`;
  p2.outro(`Project ready!
Next: ${nextSteps}`);
}
main().catch((err) => {
  console.error(err);
  process.exit(1);
});
