# Etapa de construcción
FROM node:22.12.0-slim AS builder

WORKDIR /app

# Instalar pnpm y dependencias de build
RUN corepack enable && corepack prepare pnpm@9.15.0 --activate
RUN apt-get update && apt-get install -y python3 make g++ && rm -rf /var/lib/apt/lists/*

# Copiar archivos de dependencias
COPY package.json pnpm-lock.yaml* bun.lock* ./

# Instalar dependencias (reproducible cuando hay lockfile de pnpm)
RUN if [ -f pnpm-lock.yaml ]; then \
      pnpm install --frozen-lockfile; \
    else \
      echo "WARNING: pnpm-lock.yaml no encontrado; instalando sin lockfile congelado"; \
      pnpm install --no-frozen-lockfile; \
    fi

# Copiar código fuente
COPY . .

# Construir la aplicación
RUN pnpm run build

# Etapa de producción
FROM node:22.12.0-slim

WORKDIR /app

# Copiar solo lo necesario desde builder
COPY --from=builder /app/.output /app/.output

# Exponer puerto
ENV PORT=3000
EXPOSE 3000

# Comando de inicio
CMD ["sh", "-c", "test -n \"$NUXT_API_BASE_URL\" || { echo 'ERROR: NUXT_API_BASE_URL no definida'; exit 1; }; node .output/server/index.mjs"]
