<script setup>
import { ref } from 'vue'
import axios from 'axios'

const keyword = ref('')
const loading = ref(false)
const results = ref([])
const error = ref('')

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
    } else if (response.data.results) {
      results.value = response.data.results
    } else {
      results.value = []
    }
  } catch (err) {
    console.error(err)
    error.value = 'Terjadi kesalahan saat mencari domain.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen bg-slate-50">
    <div class="mx-auto max-w-5xl px-4 py-12">
      <div class="rounded-3xl bg-white p-8 shadow-sm border border-slate-200">
        <h1 class="text-3xl font-bold text-slate-900">
          Cari Domain
        </h1>
        <p class="mt-2 text-slate-600">
          Temukan nama domain terbaik untuk website atau bisnismu.
        </p>

        <div class="mt-6 flex flex-col gap-3 md:flex-row">
          <input
            v-model="keyword"
            type="text"
            placeholder="contoh: tokobagus"
            class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-slate-900 outline-none focus:border-blue-500"
            @keyup.enter="searchDomain"
          />

          <button
            class="rounded-2xl bg-blue-600 px-6 py-3 font-semibold text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60"
            :disabled="loading"
            @click="searchDomain"
          >
            {{ loading ? 'Mencari...' : 'Cari Domain' }}
          </button>
        </div>

        <p v-if="error" class="mt-3 text-sm text-red-600">
          {{ error }}
        </p>
      </div>

      <div class="mt-8 space-y-4">
        <div
          v-for="item in results"
          :key="item.domain"
          class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
        >
          <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
              <h2 class="text-lg font-semibold text-slate-900">
                {{ item.domain }}
              </h2>

              <div v-if="item.categories?.length" class="mt-2 flex flex-wrap gap-2">
                <span
                  v-for="category in item.categories"
                  :key="category.id"
                  class="rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-700"
                >
                  {{ category.name }}
                </span>
              </div>
            </div>

            <div
              class="inline-flex rounded-full px-4 py-2 text-sm font-semibold"
              :class="item.available ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
            >
              {{ item.available ? 'Tersedia' : 'Tidak tersedia' }}
            </div>
          </div>

          <div class="mt-4 flex items-center gap-4">
            <div class="text-2xl font-bold text-slate-900">
              Rp {{ Number(item.register_price).toLocaleString('id-ID') }}
            </div>

            <div
              v-if="item.gimmick_price"
              class="text-sm text-slate-400 line-through"
            >
              Rp {{ Number(item.gimmick_price).toLocaleString('id-ID') }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>