<script setup>
const phrases = ['Gestiona', 'Organiza', 'Controla', 'Automatiza', 'Escala']
const current = ref('')
const showPipe = ref(true)
let i = 0
let typeInterval = null
let phraseInterval = null
let pipeInterval = null
let isTyping = false

function typeNext() {
  if (isTyping) return
  isTyping = true
  current.value = ''
  const phrase = phrases[i]
  let j = 0

  if (typeInterval) clearInterval(typeInterval)

  typeInterval = setInterval(() => {
    if (j < phrase.length) {
      current.value += phrase[j]
      j++
    } else {
      clearInterval(typeInterval)
      isTyping = false
    }
  }, 100)

  i = (i + 1) % phrases.length
}

function startAnimations() {
  typeNext()
  phraseInterval = setInterval(typeNext, 3000)
  pipeInterval = setInterval(() => { showPipe.value = !showPipe.value }, 500)
}

onMounted(() => {
  startAnimations()
  document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'visible') {
      if (typeInterval) clearInterval(typeInterval)
      if (phraseInterval) clearInterval(phraseInterval)
      if (pipeInterval) clearInterval(pipeInterval)
      isTyping = false
      i = 0
      current.value = ''
      showPipe.value = true
      setTimeout(startAnimations, 100)
    }
  })
})

onUnmounted(() => {
  if (typeInterval) clearInterval(typeInterval)
  if (phraseInterval) clearInterval(phraseInterval)
  if (pipeInterval) clearInterval(pipeInterval)
})
</script>

<template>
  <div class="flex flex-col gap-4 items-start text-white">
    <div class="text-5xl font-bold min-h-[3rem]">
      {{ current }}<span v-if="showPipe">|</span>
    </div>
    <div class="text-5xl">tu negocio con una sola plataforma.</div>
    <p class="text-xl mt-2">
      Simplicidad y potencia para equipos que quieren moverse rápido.
    </p>
  </div>
</template>
