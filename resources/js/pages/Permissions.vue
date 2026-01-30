<template>
  <AuthenticatedLayout>
    <Head title="Permissions" />
    
    <v-row>
      <v-col cols="12" md="8">
        <h1 class="text-h4 font-weight-bold">Permissions</h1>
        <p class="text-subtitle-1 text-medium-emphasis">Manage system permissions</p>
      </v-col>
      <v-col cols="12" md="4" class="d-flex justify-md-end">
        <v-btn
          v-if="auth.can('create-permission')"
          class="bg-primary"
          prepend-icon="mdi-plus"
          @click="openCreateModal"
        >
          Create
        </v-btn>
      </v-col>
    </v-row>

    <v-card elevation="2" >
      <v-card-title class="py-3 px-4 d-flex align-center">
        <v-text-field
          v-model="search"
          prepend-inner-icon="mdi-magnify"
          label="Search permissions"
          single-line
          hide-details
          clearable
          variant="outlined"
          density="compact"
          class="max-width-300"
        ></v-text-field>
        <v-spacer></v-spacer>
        <v-chip size="small" color="info" variant="tonal">
          Total: {{ permissions.length }}
        </v-chip>
      </v-card-title>
      
      <v-divider></v-divider>
      
      <v-card-text class="pa-0">
        <v-data-table
          :headers="headers"
          :items="Array.isArray(permissions) ? permissions : []"
          :search="search"
          :loading="loading"
          class="elevation-0"
          hover
        >
          <template v-slot:item.name="{ item }">
            <div class="d-flex align-center">
              <v-icon size="small" color="primary" class="mr-2">mdi-key</v-icon>
              {{ item.name }}
            </div>
          </template>
          
          <template v-slot:item.group_name="{ item }">
            <v-chip size="small" :color="getGroupColor(item.group_name)" variant="flat">
              {{ item.group_name }}
            </v-chip>
          </template>

          <template v-slot:item.created_at="{ item }">
            {{ formatDate(item.created_at) }}
          </template>

          <template v-slot:item.actions="{ item }">
            <v-btn
              v-if="auth.can('update-permission')"
              icon size="small" color="primary" variant="text" @click="editPermission(item)"
            >
              <v-icon>mdi-pencil</v-icon>
            </v-btn>
            <v-btn
              v-if="auth.can('delete-permission')"
              icon size="small" color="error" variant="text" @click="confirmDelete(item)"
            >
              <v-icon>mdi-delete</v-icon>
            </v-btn>
          </template>
        </v-data-table>
      </v-card-text>
    </v-card>

    <v-dialog v-model="dialog" max-width="600px" persistent>
      <v-card>
        <v-card-title class="text-h5 px-4 pt-4">
          {{ isEditing ? 'Edit Permission' : 'Create Permission' }}
        </v-card-title>
        <v-card-text>
          <v-form ref="formRef" @submit.prevent="savePermission" lazy-validation>
            <v-row>
              <v-col cols="12">
                <v-text-field
                  v-model="form.name"
                  label="Permission Name"
                  :rules="[v => !!v || 'Name is required']"
                  required
                  variant="outlined"
                  prepend-inner-icon="mdi-key"
                ></v-text-field>
              </v-col>

              <v-col cols="12">
                <v-text-field
                  v-model="form.slug"
                  label="Slug"
                  :rules="[
                    v => !!v || 'Slug is required',
                    v => /^[a-z-]+$/.test(v) || 'Slug must be lowercase with hyphens only'
                  ]"
                  required
                  variant="outlined"
                  prepend-inner-icon="mdi-tag"
                  hint="Unique identifier for the permission"
                ></v-text-field>
              </v-col>

              <v-col cols="12">
                <v-text-field
                  v-model="form.group_name"
                  label="Group Name"
                  :rules="[v => !!v || 'Group Name is required']"
                  required
                  variant="outlined"
                  prepend-inner-icon="mdi-folder"
                  hint="Categorize your permission"
                ></v-text-field>
              </v-col>
            </v-row>
          </v-form>
        </v-card-text>
        <v-card-actions class="pb-4 px-4">
          <v-spacer></v-spacer>
          <v-btn variant="text" class="bg-black" @click="closeDialog">Cancel</v-btn>
          <v-btn color="primary" variant="elevated" @click="savePermission" :loading="saving">
            {{ isEditing ? 'Update' : 'Create' }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/layout/AuthenticatedLayout.vue'
import axios from 'axios'
import Swal from 'sweetalert2'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const permissions = ref([])
const search = ref('')
const dialog = ref(false)
const loading = ref(false)
const saving = ref(false)
const isEditing = ref(false)
const formRef = ref(null)
const groupColors = {}

const form = ref({
  id: null,
  name: '',
  slug: '',
  group_name: ''
})

const headers = ref([
  { title: 'ID', key: 'id', sortable: true, width: 80 },
  { title: 'Name', key: 'name', sortable: true },
  { title: 'Slug', key: 'slug', sortable: true },
  { title: 'Group', key: 'group_name', sortable: true },
  { title: 'Created', key: 'created_at', sortable: true },
  { title: 'Actions', key: 'actions', sortable: false, align: 'center', width: 120 }
])

const getGroupColor = (group) => {
  if (!groupColors[group]) {
    const colors = ['primary', 'success', 'warning', 'error', 'info', 'purple', 'teal', 'orange']
    groupColors[group] = colors[Object.keys(groupColors).length % colors.length]
  }
  return groupColors[group]
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const fetchPermissions = async () => {
  loading.value = true
  try {
    const response = await axios.get('/api/permissions')
    permissions.value = response.data.data || response.data
  } catch (error) {
    Swal.fire('Error', 'Failed to fetch permissions', 'error')
  } finally {
    loading.value = false
  }
}

const openCreateModal = () => {
  isEditing.value = false
  form.value = { id: null, name: '', slug: '', group_name: '' }
  dialog.value = true
}

const editPermission = (permission) => {
  isEditing.value = true
  form.value = { ...permission }
  dialog.value = true
}

const savePermission = async () => {
  const { valid } = await formRef.value.validate()
  if (!valid) return

  saving.value = true
  try {
    if (isEditing.value) {
      await axios.put(`/api/permissions/${form.value.id}`, form.value)
    } else {
      await axios.post('/api/permissions', form.value)
    }

    Swal.fire({
      icon: 'success',
      title: 'Success',
      text: `Permission ${isEditing.value ? 'updated' : 'created'} successfully`,
      timer: 2000,
      showConfirmButton: false
    })

    await fetchPermissions()
    closeDialog()
  } catch (error) {
    const message = error.response?.data?.message || 'Validation Error'
    const errors = error.response?.data?.errors

    let errorText = message
    if (errors) {
      errorText = Object.values(errors).flat().join('<br>')
    }

    Swal.fire({
      icon: 'error',
      title: 'Error',
      html: errorText
    })
  } finally {
    saving.value = false
  }
}

const confirmDelete = (item) => {
  Swal.fire({
    title: 'Are you sure?',
    text: `You are about to delete ${item.name}`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Yes, delete it!'
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        await axios.delete(`/api/permissions/${item.id}`)
        Swal.fire('Deleted!', 'Permission has been deleted.', 'success')
        fetchPermissions()
      } catch (error) {
        Swal.fire('Error', 'Failed to delete permission', 'error')
      }
    }
  })
}

const closeDialog = () => {
  dialog.value = false
  if (formRef.value) formRef.value.reset()
}

onMounted(() => {
  fetchPermissions()
})
</script>
