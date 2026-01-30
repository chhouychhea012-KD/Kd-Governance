<template>
  <AuthenticatedLayout>
    <Head title="Dashboard" />

    <v-row class="mb-4">
      <v-col cols="12">
        <h1 class="text-h4 font-weight-bold">Dashboard</h1>
        <p class="text-subtitle-1 text-medium-emphasis">
          Performance metrics by Team and User
        </p>
      </v-col>
    </v-row>

    <v-row>
      <v-col cols="12" sm="6" md="3">
        <v-card elevation="2" color="indigo-darken-2" theme="dark">
          <v-card-text class="d-flex justify-space-between align-center">
            <div>
              <p class="text-subtitle-2 mb-1">Target Score</p>
              <h2 class="text-h4 font-weight-bold">{{ stats.targetScore }}</h2>
            </div>
            <v-icon size="48" color="rgba(255,255,255,0.3)">mdi-target</v-icon>
          </v-card-text>
        </v-card>
      </v-col>
      <v-col cols="12" sm="6" md="3">
        <v-card elevation="2">
          <v-card-text class="d-flex justify-space-between align-center">
            <div>
              <p class="text-subtitle-2 text-medium-emphasis mb-1">Total Users</p>
              <h2 class="text-h4 font-weight-bold">{{ stats.totalUsers }}</h2>
            </div>
            <v-avatar color="primary" size="48">
              <v-icon>mdi-account-group</v-icon>
            </v-avatar>
          </v-card-text>
        </v-card>
      </v-col>
      <v-col cols="12" sm="6" md="3">
        <v-card elevation="2">
          <v-card-text class="d-flex justify-space-between align-center">
            <div>
              <p class="text-subtitle-2 text-medium-emphasis mb-1">Total Teams</p>
              <h2 class="text-h4 font-weight-bold">{{ stats.totalTeams }}</h2>
            </div>
            <v-avatar color="success" size="48">
              <v-icon>mdi-account-multiple</v-icon>
            </v-avatar>
          </v-card-text>
        </v-card>
      </v-col>
      <v-col cols="12" sm="6" md="3">
        <v-card elevation="2">
          <v-card-text class="d-flex justify-space-between align-center">
            <div>
              <p class="text-subtitle-2 text-medium-emphasis mb-1">Total Evaluations</p>
              <h2 class="text-h4 font-weight-bold">{{ stats.totalEvaluations }}</h2>
            </div>
            <v-avatar color="warning" size="48">
              <v-icon>mdi-star</v-icon>
            </v-avatar>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <v-card elevation="2" class="mt-6">
      <v-card-text>
        <v-row>
          <v-col cols="12" md="4">
            <v-select
              v-model="selectedTeamId"
              :items="teams"
              item-title="name"
              item-value="id"
              label="Select Team"
              variant="outlined"
              density="compact"
              hide-details
              @update:model-value="onTeamChange"
            />
          </v-col>
          <v-col cols="12" md="4">
            <v-select
              v-model="selectedUserId"
              :items="availableUsers"
              item-title="name"
              item-value="id"
              label="Select User"
              variant="outlined"
              density="compact"
              hide-details
              :disabled="!availableUsers.length"
              @update:model-value="fetchUserChartData"
            />
          </v-col>
          <v-col cols="12" md="4">
            <v-select
              v-model="chartFilters.year"
              :items="years"
              label="Year"
              variant="outlined"
              density="compact"
              hide-details
              @update:model-value="fetchUserChartData"
            />
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>

    <v-row class="mt-4">
      <v-col cols="12" md="6">
        <v-card elevation="2">
          <v-card-title class="text-subtitle-1">Monthly Evaluation Scores</v-card-title>
          <v-card-text>
            <div style="height: 350px">
              <Bar v-if="chartsReady" :data="evalChartData" :options="barOptions" />
              <div v-else class="d-flex align-center justify-center fill-height">
                No data
              </div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" md="6">
        <v-card elevation="2">
          <v-card-title class="text-subtitle-1">Monthly Improvement Count</v-card-title>
          <v-card-text>
            <div style="height: 350px">
              <Bar v-if="chartsReady" :data="impChartData" :options="improvementOptions" />
              <div v-else class="d-flex align-center justify-center fill-height">
                No data
              </div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <v-row class="mt-4">
      <v-col cols="12">
        <v-card elevation="2">
          <v-card-title class="py-4 px-6 d-flex align-center">
            <v-icon class="mr-2">mdi-history</v-icon>
            Recent Evaluations
          </v-card-title>
          <v-divider />
          
          <v-row class="px-6 pt-4">
            <v-col cols="12" sm="4">
              <v-select v-model="tableFilters.year" :items="[2024, 2025, 2026]" label="Filter Year" variant="outlined" density="compact" @update:model-value="fetchTableData" />
            </v-col>
            <v-col cols="12" sm="4">
              <v-select v-model="tableFilters.quarter" :items="quarterOptions" label="Filter Quarter" variant="outlined" density="compact" clearable @update:model-value="handleQuarterChange" />
            </v-col>
            <v-col cols="12" sm="4">
              <v-select v-model="tableFilters.month" :items="monthOptions" label="Filter Month" variant="outlined" density="compact" clearable @update:model-value="handleMonthChange" />
            </v-col>
          </v-row>

          <v-data-table-server
            v-model:page="page"
            v-model:items-per-page="itemsPerPage"
            :headers="tableHeaders"
            :items="tableItems"
            :items-length="tableTotal"
            :items-per-page-options="itemsPerPageOptions"
            :loading="tableLoading"
            class="elevation-0"
            @update:options="fetchTableData"
          >
            <template #item.score="{ item }">
              <v-chip :color="getScoreColor(item.score)" size="small" variant="flat">
                {{ item.score }}
              </v-chip>
            </template>

            <template #item.created_at="{ item }">
              {{ formatDate(item.created_at) }}
            </template>
          </v-data-table-server>

        </v-card>
      </v-col>
    </v-row>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted,computed} from 'vue'
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/layout/AuthenticatedLayout.vue'
import axios from 'axios'
import { Bar } from 'vue-chartjs'
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale
} from 'chart.js'
const years = computed(() => {
  const startYear = 2020
  const currentYear = new Date().getFullYear()

  return Array.from(
    { length: currentYear - startYear + 1 },
    (_, i) => startYear + i
  ).reverse()
})
ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale)

/* --- STATE --- */
const stats = ref({ totalUsers: 0, totalTeams: 0, totalEvaluations: 0, totalImprovements: 0, targetScore: 0 })
const teams = ref([])
const availableUsers = ref([])
const selectedTeamId = ref(null)
const selectedUserId = ref(null)
const chartsReady = ref(false)

const tableLoading = ref(false)
const tableItems = ref([])
const tableFilters = ref({ year: new Date().getFullYear(), quarter: null, month: null })
const tableTotal = ref(0)
const itemsPerPage = ref(10)
const page = ref(1)
const itemsPerPageOptions = ref([5, 10, 25, 50, 100])

const chartFilters = ref({ year: new Date().getFullYear() })
const rawImprovementList = ref([])
const evalChartData = ref({ labels: [], datasets: [] })
const impChartData = ref({ labels: [], datasets: [] })

/* --- CHART CONFIG --- */
const barOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { display: false } },
  scales: { y: { beginAtZero: true } }
}

const improvementOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: {
      callbacks: {
        afterBody: (context) => {
          const monthIndex = context[0].dataIndex + 1
          const monthDetails = rawImprovementList.value
            .filter(item => new Date(item.created_at).getMonth() + 1 === monthIndex)
            .map(item => `• ${item.improvement}`)
            .join('\n')
          
          return monthDetails ? `Details:\n${monthDetails}` : 'No details'
        }
      }
    }
  },
  scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
}

/* --- TABLE CONFIG --- */
const tableHeaders = [
  { title: 'User', key: 'user.name' },
  { title: 'Question', key: 'question' },
  { title: 'Score', key: 'score' },
  { title: 'Date', key: 'created_at' }
]

const quarterOptions = [
  { title: 'Q1', value: 1 }, { title: 'Q2', value: 2 }, { title: 'Q3', value: 3 }, { title: 'Q4', value: 4 }
]

const monthOptions = [
  { title: 'Jan', value: 1 }, { title: 'Feb', value: 2 }, { title: 'Mar', value: 3 },
  { title: 'Apr', value: 4 }, { title: 'May', value: 5 }, { title: 'Jun', value: 6 },
  { title: 'Jul', value: 7 }, { title: 'Aug', value: 8 }, { title: 'Sep', value: 9 },
  { title: 'Oct', value: 10 }, { title: 'Nov', value: 11 }, { title: 'Dec', value: 12 }
]

/* --- METHODS --- */
const onTeamChange = () => {
  const team = teams.value.find(t => t.id === selectedTeamId.value)
  availableUsers.value = team ? team.users : []
  if (availableUsers.value.length > 0) {
    selectedUserId.value = availableUsers.value[0].id
    fetchUserChartData()
  } else {
    selectedUserId.value = null
    chartsReady.value = false
  }
}

const fetchUserChartData = async () => {
  if (!selectedUserId.value) return
  chartsReady.value = false
  try {
    const { data } = await axios.get('/api/dashboard/chart-data', {
      params: { user_id: selectedUserId.value, year: chartFilters.value.year }
    })
    
    rawImprovementList.value = data.improvementList || []

    evalChartData.value = {
      labels: data.labels,
      datasets: [{
        label: 'Average Score',
        backgroundColor: '#1E88E5',
        data: data.scores
      }]
    }

    impChartData.value = {
      labels: data.labels, // Months bottom
      datasets: [{
        label: 'Improvement Count',
        backgroundColor: '#43A047',
        data: data.improvements // Label : Count
      }]
    }

    chartsReady.value = true
  } catch (e) {
    console.error(e)
  }
}

/* --- Updated fetchTableData Method --- */

const fetchTableData = async (options = {}) => {
  tableLoading.value = true

  try {
    if (options.page) page.value = options.page
    if (options.itemsPerPage) itemsPerPage.value = options.itemsPerPage

    const params = {
      year: tableFilters.value.year,
      quarter: tableFilters.value.quarter,
      month: tableFilters.value.month,
      page: page.value,
      per_page: itemsPerPage.value
    }

    const { data } = await axios.get('/api/dashboard/recent-evaluations', { params })

    tableItems.value = data.data
    tableTotal.value = data.total
  } catch (e) {
    console.error('Failed to fetch table data:', e)
  } finally {
    tableLoading.value = false
  }
}


// Update filter handlers to reset to page 1 when filters change
const handleQuarterChange = () => { 
  if (tableFilters.value.quarter) tableFilters.value.month = null
  page.value = 1 
  fetchTableData() 
}

const handleMonthChange = () => { 
  if (tableFilters.value.month) tableFilters.value.quarter = null
  page.value = 1
  fetchTableData() 
}
const getScoreColor = (s) => (s >= stats.value.targetScore ? 'success' : s >= 60 ? 'warning' : 'error')
const formatDate = (d) => new Date(d).toLocaleDateString()
const getStatsAndTeamUser = async () => {
  try {
    // Now this will only run once auth.isInitialized is true
    const [s, t] = await Promise.all([
       axios.get('/api/dashboard/stats'),
       axios.get('/api/dashboard/teams-users')
    ])
    
    stats.value = s.data
    teams.value = t.data
    
    if (teams.value.length > 0) {
      selectedTeamId.value = teams.value[0].id
      onTeamChange()
    }
    fetchTableData()
  } catch (e) {
    console.error("Dashboard failed to load data", e)
  }
}

onMounted(() => {
  // getStatsAndTeamUser is safe to call now
  getStatsAndTeamUser()
})
// getStatsAndTeamUser();
// onMounted(() => {
//   console.error(e)
//   }
// })
</script>