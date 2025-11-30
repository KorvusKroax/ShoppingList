# Build stage
FROM node:20-alpine AS builder

WORKDIR /app
COPY package.json package-lock.json ./
RUN npm install
COPY . .
RUN npm run build

# Run stage
FROM node:20-alpine AS runner

WORKDIR /app

# Másolja az épített Next.js appot
COPY --from=builder /app/.next ./.next
COPY --from=builder /app/node_modules ./node_modules
COPY --from=builder /app/package.json ./package.json
COPY --from=builder /app/public ./public

# A fejlesztési szerver portja (gyakran 3000)
EXPOSE 3000

CMD ["npm", "run", "dev"]
