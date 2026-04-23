<script setup>
import { computed, onMounted, ref } from 'vue'
import axios from 'axios'

const keyword = ref('')
const loading = ref(false)
const loadingConfig = ref(false)
const error = ref('')
const results = ref([])
const spotlight = ref([])
const categories = ref([])
const config = ref({})

const hasResults = computed(() => results.value.length > 0)

const formatPrice = (value) => {
  if (value === null || value === undefined || value === '') return '-'
  return 'Rp ' + Number(value).toLocaleString('id-ID')
}

const loadConfig = async () => {
  loadingConfig.value = true
  try {
    const response = await axios.get('/api/domain/config')

    config.value = response.data.config ?? {}
    categories.value = response.data.categories ?? []

    const spotlightRaw = response.data.config?.SEARCH_DOMAIN_SPOTLIGHT
      ?? response.data.config?.search_domain_spotlight
      ?? []

    if (Array.isArray(spotlightRaw)) {
      spotlight.value = spotlightRaw
    } else {
      spotlight.value = ['.com', '.id', '.co.id', '.my.id', '.net']
    }
  } catch (err) {
    console.error('Load config error:', err)
    spotlight.value = ['.com', '.id', '.co.id', '.my.id', '.net']
  } finally {
    loadingConfig.value = false
  }
}

const searchDomain = async () => {
  error.value = ''
  results.value = []

  if (!keyword.value.trim()) {
    error.value = 'Masukkan nama domain terlebih dahulu.'
    return
  }

  loading.value = true

  try {
    const response = await axios.post('/api/domain/check', {
      keyword: keyword.value.trim(),
    })

    if (Array.isArray(response.data)) {
      results.value = response.data
    } else {
      results.value = response.data.results ?? []
    }
  } catch (err) {
    console.error('Search error:', err)
    error.value = 'Terjadi kesalahan saat mencari domain.'
  } finally {
    loading.value = false
  }
}

const useSpotlight = (ext) => {
  const clean = String(ext).replace(/^\./, '')
  keyword.value = keyword.value.trim()
  if (!keyword.value) {
    error.value = 'Masukkan nama domain terlebih dahulu.'
    return
  }

  results.value = [
    {
      domain: `${keyword.value}.${clean}`,
      available: null,
      register_price: null,
      gimmick_price: null,
      categories: [],
    },
  ]

  searchDomain()
}

onMounted(() => {
  loadConfig()
})
</script>

<template>
  <div class="min-h-screen bg-slate-50 text-slate-900">
    <section class="border-b border-slate-200 bg-gradient-to-b from-blue-50 to-white">
      <div class="mx-auto max-w-6xl px-4 py-14 md:py-20">
        <div class="mx-auto max-w-3xl text-center">
          <p class="inline-flex rounded-full bg-blue-100 px-4 py-1 text-sm font-medium text-blue-700">
            Domain Search App
          </p>

          <h1 class="mt-5 text-4xl font-bold tracking-tight md:text-5xl">
            Cari nama domain terbaik untuk bisnismu
          </h1>

          <p class="mt-4 text-lg text-slate-600">
            Cek ketersediaan domain, lihat harga registrasi, dan temukan ekstensi yang paling cocok untuk brand kamu.
          </p>
        </div>

        <div class="mx-auto mt-10 max-w-4xl rounded-3xl border border-slate-200 bg-white p-4 shadow-sm md:p-5">
          <div class="flex flex-col gap-3 md:flex-row">
            <div class="relative flex-1">
              <input
                v-model="keyword"
                type="text"
                placeholder="contoh: tokobagus"
                class="w-full rounded-2xl border border-slate-300 px-5 py-4 text-lg outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                @keyup.enter="searchDomain"
              />
            </div>

            <button
              class="rounded-2xl bg-blue-600 px-6 py-4 text-base font-semibold text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60"
              :disabled="loading"
              @click="searchDomain"
            >
              {{ loading ? 'Mencari...' : 'Cari Domain' }}
            </button>
          </div>

          <p v-if="error" class="mt-3 text-sm text-red-600">
            {{ error }}
          </p>

          <div class="mt-5">
            <p class="mb-3 text-sm font-medium text-slate-500">
              Ekstensi populer
            </p>

            <div class="flex flex-wrap gap-2">
              <button
                v-for="item in spotlight"
                :key="item"
                type="button"
                class="rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-blue-400 hover:text-blue-700"
                @click="useSpotlight(item)"
              >
                {{ item.startsWith('.') ? item : `.${item}` }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="mx-auto max-w-6xl px-4 py-10">
      <div class="mb-6 flex items-center justify-between">
        <div>
          <h2 class="text-2xl font-bold">Hasil Pencarian</h2>
          <p class="mt-1 text-sm text-slate-500">
            Hasil domain yang cocok untuk keyword yang kamu cari.
          </p>
        </div>
      </div>

      <div v-if="loading" class="grid gap-4">
        <div
          v-for="n in 5"
          :key="n"
          class="animate-pulse rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
        >
          <div class="flex items-center justify-between gap-4">
            <div class="space-y-3">
              <div class="h-5 w-44 rounded bg-slate-200"></div>
              <div class="h-4 w-28 rounded bg-slate-100"></div>
            </div>
            <div class="h-8 w-28 rounded-full bg-slate-200"></div>
          </div>
          <div class="mt-4 h-6 w-36 rounded bg-slate-200"></div>
        </div>
      </div>

      <div
        v-else-if="!hasResults"
        class="rounded-3xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center"
      >
        <div class="mx-auto max-w-xl">
          <h3 class="text-xl font-semibold text-slate-900">
            Belum ada hasil pencarian
          </h3>
          <p class="mt-2 text-slate-600">
            Masukkan nama domain di atas, lalu klik tombol cari untuk melihat ketersediaan dan harga domain.
          </p>
        </div>
      </div>

      <div v-else class="grid gap-4">
        <div
          v-for="item in results"
          :key="item.domain"
          class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:shadow-md"
        >
          <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="min-w-0">
              <h3 class="truncate text-xl font-semibold text-slate-900">
                {{ item.domain }}
              </h3>

              <div v-if="item.categories?.length" class="mt-3 flex flex-wrap gap-2">
                <span
                  v-for="category in item.categories"
                  :key="category.id"
                  class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700"
                >
                  {{ category.name }}
                </span>
              </div>
            </div>

            <div
              class="inline-flex w-fit rounded-full px-4 py-2 text-sm font-semibold"
              :class="item.available === true
                ? 'bg-green-100 text-green-700'
                : item.available === false
                  ? 'bg-red-100 text-red-700'
                  : 'bg-yellow-100 text-yellow-700'"
            >
              {{
                item.available === true
                  ? 'Tersedia'
                  : item.available === false
                    ? 'Tidak tersedia'
                    : 'Memeriksa'
              }}
            </div>
          </div>

          <div class="mt-5 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-4">
              <div class="text-2xl font-bold text-slate-900">
                {{ formatPrice(item.register_price) }}
              </div>

              <div
                v-if="item.gimmick_price"
                class="text-sm text-slate-400 line-through"
              >
                {{ formatPrice(item.gimmick_price) }}
              </div>
            </div>

            <button
              class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800"
              type="button"
            >
              Pilih Domain
            </button>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>