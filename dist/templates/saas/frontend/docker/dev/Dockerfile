FROM node:22.12.0-slim

WORKDIR /app

# Instalar pnpm
RUN corepack enable && corepack prepare pnpm@9.15.0 --activate
RUN apt-get update && apt-get install -y python3 make g++ && rm -rf /var/lib/apt/lists/*

# Copiar archivos de dependencias
COPY package.json pnpm-lock.yaml* ./

# Instalar dependencias
RUN pnpm install --no-frozen-lockfile

# Exponer puerto
ENV PORT=3000
EXPOSE 3000

# El código será montado como volumen en compose.yml
# Comando de desarrollo con hot reload
CMD ["pnpm", "run", "dev", "--host", "0.0.0.0"]
