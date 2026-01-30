<template>
  <AuthenticatedLayout>
    <Head title="Teams" />
    <v-row class="mb-4">
      <v-col cols="12" md="8">
        <h1 class="text-h4 font-weight-bold">Teams</h1>
        <p class="text-subtitle-1 text-medium-emphasis">Manage teams and their members</p>
      </v-col>
      <v-col cols="12" md="4" class="text-md-right">
        <v-btn
          v-if="$page.props.permissions.some(p => p.slug === 'create-teams')"
          color=""
          class="bg-primary"
          prepend-icon="mdi-plus"
          @click="openCreateModal"
        >
          Create Team
        </v-btn>
      </v-col>
    </v-row>

    <v-row>
      <v-col cols="12">
        <v-card elevation="2">
          <v-card-title>
            <v-row>
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="search"
                  prepend-inner-icon="mdi-magnify"
                  label="Search teams"
                  single-line
                  hide-details
                  clearable
                  variant="outlined"
                  density="compact"
                ></v-text-field>
              </v-col>
            </v-row>
          </v-card-title>
          <v-card-text>
            <v-data-table
              :headers="headers"
              :items="teams"
              :search="search"
              :loading="loading"
              class="elevation-0"
            >
              <template v-slot:item.actions="{ item }">
                <v-btn
                  v-if="$page.props.permissions.some(p => p.slug === 'update-teams')"
                  icon size="small" color="primary" variant="text" @click="editTeam(item)"
                >
                  <v-icon>mdi-pencil</v-icon>
                </v-btn>
                <v-btn
                  v-if="$page.props.permissions.some(p => p.slug === 'delete-teams')"
                  icon size="small" color="error" variant="text" @click="confirmDelete(item)"
                >
                  <v-icon>mdi-delete</v-icon>
                </v-btn>
              </template>
              <template v-slot:item.parent="{ item }">
                <v-chip v-if="item.parent" color="secondary" size="small">
                  {{ item.parent.name }}
                </v-chip>
                <span v-else class="text-medium-emphasis">-</span>
              </template>
              <template v-slot:item.created_at="{ item }">
                {{ formatDate(item.created_at) }}
              </template>
            </v-data-table>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Create/Edit Dialog -->
    <v-dialog v-model="dialog" max-width="600px" persistent>
      <v-card>
        <v-card-title class="text-h5">
          {{ isEditing ? 'Edit Team' : 'Create Team' }}
        </v-card-title>
        <v-card-text>
          <v-form ref="formRef" @submit.prevent="saveTeam">
            <v-text-field
              v-model="form.name"
              label="Team Name"
              :rules="[v => !!v || 'Name is required']"
              required
              variant="outlined"
              prepend-inner-icon="mdi-account-group"
            ></v-text-field>
            <v-select
              v-model="form.parent_id"
              :items="availableParentTeams"
              item-title="name"
              item-value="id"
              label="Parent Team"
              variant="outlined"
              prepend-inner-icon="mdi-sitemap"
              clearable
              hint="Select a parent team to create a sub-team"
              persistent-hint
            ></v-select>
          </v-form>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn variant="text" class="bg-black" @click="closeDialog">Cancel</v-btn>
          <v-btn color="primary" variant="elevated" @click="saveTeam" :loading="saving">
            {{ isEditing ? 'Update' : 'Create' }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Delete Confirmation Dialog -->
    <v-dialog v-model="deleteDialog" max-width="500px">
      <v-card>
        <v-card-title class="text-h5">Confirm Delete</v-card-title>
        <v-card-text>
          Are you sure you want to delete this team? This action cannot be undone.
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn variant="text" @click="deleteDialog = false">Cancel</v-btn>
          <v-btn color="error" variant="elevated" @click="deleteTeam" :loading="deleting">Delete</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/layout/AuthenticatedLayout.vue'
import axios from 'axios'

const teams = ref([])
const parentTeams = ref([])
const search = ref('')
const dialog = ref(false)
const deleteDialog = ref(false)
const loading = ref(false)
const saving = ref(false)
const deleting = ref(false)
const isEditing = ref(false)
const itemToDelete = ref(null)
const formRef = ref(null)

const form = ref({
  id: null,
  name: '',
  parent_id: null
})

// Computed property to get available parent teams (exclude current team when editing)
const availableParentTeams = computed(() => {
  if (isEditing.value && form.value.id) {
    return parentTeams.value.filter(team => team.id !== form.value.id)
  }
  return parentTeams.value
})

const headers = ref([
  { title: 'ID', key: 'id', sortable: true },
  { title: 'Name', key: 'name', sortable: true },
  { title: 'Parent Team', key: 'parent', sortable: false },
  { title: 'Created At', key: 'created_at', sortable: true },
  { title: 'Actions', key: 'actions', sortable: false, align: 'center' }
])

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const fetchTeams = async () => {
  loading.value = true
  try {
    // Fetch all teams for the data table
    const response = await axios.get('/api/teams')
    teams.value = response.data?.data || response.data
    
    // Fetch only parent teams (without parent_id) for the parent select
    const parentResponse = await axios.get('/api/teams/parent')
    parentTeams.value = parentResponse.data?.data || parentResponse.data
  } catch (error) {
    console.error('Error fetching teams:', error)
  } finally {
    loading.value = false
  }
}

const openCreateModal = () => {
  isEditing.value = false
  form.value = { id: null, name: '', parent_id: null }
  dialog.value = true
}

const editTeam = (team) => {
  isEditing.value = true
  form.value = { 
    id: team.id, 
    name: team.name, 
    parent_id: team.parent_id || null 
  }
  dialog.value = true
}

const saveTeam = async () => {
  const { valid } = await formRef.value.validate()
  if (!valid) return

  saving.value = true
  try {
    if (isEditing.value) {
      await axios.put(`/api/teams/${form.value.id}`, form.value)
    } else {
      await axios.post('/api/teams', form.value)
    }
    await fetchTeams()
    closeDialog()
  } catch (error) {
    const message = error.response?.data?.error || 'Failed to save team'
    alert(message)
    console.error('Error saving team:', error)
  } finally {
    saving.value = false
  }
}

const confirmDelete = (item) => {
  itemToDelete.value = item
  deleteDialog.value = true
}

const deleteTeam = async () => {
  deleting.value = true
  try {
    await axios.delete(`/api/teams/${itemToDelete.value.id}`)
    await fetchTeams()
    deleteDialog.value = false
  } catch (error) {
    console.error('Error deleting team:', error)
  } finally {
    deleting.value = false
  }
}

const closeDialog = () => {
  dialog.value = false
  form.value = { id: null, name: '', parent_id: null }
  if (formRef.value) {
    formRef.value.reset()
  }
}

onMounted(() => {
  fetchTeams()
})
</script>
