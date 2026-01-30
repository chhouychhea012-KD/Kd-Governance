<template>
  <AuthenticatedLayout>
    <Head title="Settings" />
    <v-row>
      <v-col cols="12" md="8">
        <h1 class="text-h4 font-weight-bold ">Settings</h1>
        <p class="text-subtitle-1 text-medium-emphasis">Manage application settings</p>
      </v-col>
      <v-col cols="12" md="4" class="d-flex justify-md-end">
        <v-btn 
          class="bg-primary"
          prepend-icon="mdi-plus" 
          @click="openCreateModal"
        >
          Add Setting
        </v-btn>
      </v-col>
    </v-row>

    <v-card elevation="2" >
      <v-card-title class="py-3 px-4 d-flex align-center">
        <v-text-field
          v-model="search"
          prepend-inner-icon="mdi-magnify"
          label="Search settings"
          single-line
          hide-details
          clearable
          variant="outlined"
          density="compact"
          class="max-width-300"
        ></v-text-field>
        <v-spacer></v-spacer>
        <v-chip size="small" color="info" variant="tonal">
          Total: {{ settings.length }}
        </v-chip>
      </v-card-title>
      
      <v-divider></v-divider>
      
      <v-card-text class="pa-0">
        <v-data-table
          :headers="headers"
          :items="settings"
          :search="search"
          :loading="loading"
          class="elevation-0"
          hover
        >
          <template v-slot:item.value="{ item }">
            <div class="text-truncate" style="max-width: 200px;">
              {{ item.value || '-' }}
            </div>
          </template>
          <template v-slot:item.actions="{ item }">
            <v-btn icon size="small" color="primary" variant="text" @click="editSetting(item)">
              <v-icon>mdi-pencil</v-icon>
            </v-btn>
            <v-btn icon size="small" color="error" variant="text" @click="confirmDelete(item)">
              <v-icon>mdi-delete</v-icon>
            </v-btn>
          </template>
        </v-data-table>
      </v-card-text>
    </v-card>

    <v-dialog v-model="dialog" max-width="600px" persistent>
      <v-card>
        <v-card-title class="text-h5 px-4 pt-4">
          {{ isEditing ? 'Edit Setting' : 'Create Setting' }}
        </v-card-title>
        <v-card-text>
          <v-form ref="formRef">
            <v-text-field
              v-model="form.name"
              label="Name"
              :rules="[v => !!v || 'Name is required']"
              required
              variant="outlined"
              prepend-inner-icon="mdi-tag"
            ></v-text-field>
            <v-text-field
              v-model="form.key"
              label="Key"
              :rules="[v => !!v || 'Key is required']"
              required
              variant="outlined"
              prepend-inner-icon="mdi-key"
              :readonly="isEditing"
              hint="Keys are unique identifiers and cannot be changed after creation"
              persistent-hint
            ></v-text-field>
            <v-textarea
              v-model="form.value"
              label="Value"
              variant="outlined"
              prepend-inner-icon="mdi-text"
              rows="3"
              class="mt-4"
            ></v-textarea>
          </v-form>
        </v-card-text>
        <v-card-actions class="pb-4 px-4">
          <v-spacer></v-spacer>
          <v-btn variant="text" class="bg-black" @click="closeDialog">Cancel</v-btn>
          <v-btn color="primary" variant="elevated" @click="saveSetting" :loading="saving">
            {{ isEditing ? 'Update' : 'Create' }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-dialog v-model="deleteDialog" max-width="500px">
      <v-card>
        <v-card-title class="text-h5 px-4 pt-4">Confirm Delete</v-card-title>
        <v-card-text class="pa-4">
          Are you sure you want to delete this setting? This action cannot be undone.
        </v-card-text>
        <v-card-actions class="pb-4 px-4">
          <v-spacer></v-spacer>
          <v-btn variant="text" class="bg-black" @click="deleteDialog = false">Cancel</v-btn>
          <v-btn color="error" variant="elevated" @click="deleteSetting" :loading="deleting">Delete</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/layout/AuthenticatedLayout.vue'
import axios from 'axios'

const settings = ref([])
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
  key: '',
  value: ''
})

const headers = [
  { title: 'ID', key: 'id', sortable: true },
  { title: 'Name', key: 'name', sortable: true },
  { title: 'Key', key: 'key', sortable: true },
  { title: 'Value', key: 'value', sortable: false },
  { title: 'Actions', key: 'actions', sortable: false, align: 'center' }
]

const fetchSettings = async () => {
  loading.value = true
  try {
    const response = await axios.get('/api/general-settings')
    settings.value = response.data
  } catch (error) {
    console.error('Error fetching settings:', error)
  } finally {
    loading.value = false
  }
}

const keyToSlug = (key) => {
  return key
    .toString()
    .trim()
    .toLowerCase()
    .replace(/[\s_]+/g, '-')
    .replace(/[^\w-]+/g, '')
    .replace(/--+/g, '-')
    .replace(/^-+|-+$/g, '')
}

const openCreateModal = () => {
  isEditing.value = false
  form.value = { id: null, name: '', key: '', value: '' }
  dialog.value = true
}

const editSetting = (setting) => {
  isEditing.value = true
  form.value = { ...setting }
  dialog.value = true
}

const saveSetting = async () => {
  const { valid } = await formRef.value.validate()
  if (!valid) return
  
  saving.value = true
  try {
    if (isEditing.value) {
      await axios.put(`/api/general-settings/${form.value.id}`, form.value)
    } else {
      await axios.post('/api/general-settings', form.value)
    }
    await fetchSettings()
    closeDialog()
  } catch (error) {
    console.error('Error saving setting:', error)
  } finally {
    saving.value = false
  }
}

const confirmDelete = (item) => {
  itemToDelete.value = item
  deleteDialog.value = true
}

const deleteSetting = async () => {
  deleting.value = true
  try {
    await axios.delete(`/api/general-settings/${itemToDelete.value.id}`)
    await fetchSettings()
    deleteDialog.value = false
  } catch (error) {
    console.error('Error deleting setting:', error)
  } finally {
    deleting.value = false
  }
}

const closeDialog = () => {
  dialog.value = false
  if (formRef.value) formRef.value.reset()
}

watch(() => form.value.name, (newName) => {
  if (newName && !isEditing.value) {
    form.value.key = keyToSlug(newName)
  }
})

onMounted(fetchSettings)
</script>
