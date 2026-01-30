<template>
  <AuthenticatedLayout>
    <Head title="Role Permissions" />
    <v-row class="mb-4">
      <v-col cols="12" md="8">
        <h1 class="text-h4 font-weight-bold">Role Permissions</h1>
        <p class="text-subtitle-1 text-medium-emphasis">Manage role permissions</p>
      </v-col>
    </v-row>

    <v-row>
      <v-col cols="12">
        <v-card elevation="2">
          <v-card-title>
            <v-row>
              <v-col cols="12" md="4">
                <v-select
                  v-model="selectedRole"
                  :items="roles"
                  item-title="name"
                  item-value="id"
                  label="Filter by Role"
                  variant="outlined"
                  density="compact"
                  clearable
                  hide-details
                  @update:model-value="fetchRolePermissions"
                ></v-select>
              </v-col>
            </v-row>
          </v-card-title>
          <v-card-text>
            <v-alert
              v-if="selectedRole && roles.find(r => r.id === selectedRole)?.slug === 'super-admin'"
              type="info"
              variant="tonal"
              class="mb-4"
            >
              Super Admin role has all permissions automatically and cannot be modified.
            </v-alert>
            <v-data-table
              :headers="headers"
              :items="rolePermissions"
              :loading="loading"
              class="elevation-0"
            >
              <template v-slot:item.role="{ item }">
                <v-chip v-if="item.role" color="primary" size="small">
                  {{ item.role.name }}
                </v-chip>
                <span v-else class="text-medium-emphasis">N/A</span>
              </template>
              <template v-slot:item.permission="{ item }">
                <v-chip v-if="item.permission" color="secondary" size="small">
                  {{ item.permission.name }}
                </v-chip>
                <span v-else class="text-medium-emphasis">N/A</span>
              </template>
              <template v-slot:item.actions="{ item }">
                <v-btn
                  v-if="item.role && item.role.slug !== 'super-admin'"
                  icon
                  size="small"
                  color="error"
                  variant="text"
                  @click="confirmDelete(item)"
                >
                  <v-icon>mdi-delete</v-icon>
                </v-btn>
                <v-tooltip v-else text="Cannot modify super-admin permissions" location="top">
                  <template v-slot:activator="{ props }">
                    <v-btn
                      v-bind="props"
                      icon
                      size="small"
                      color="grey"
                      variant="text"
                      disabled
                    >
                      <v-icon>mdi-lock</v-icon>
                    </v-btn>
                  </template>
                </v-tooltip>
              </template>
            </v-data-table>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <v-dialog v-model="dialog" max-width="500px" persistent>
      <v-card>
        <v-card-title class="text-h5 px-4 pt-4">
          Add Role Permission
        </v-card-title>
        <v-card-text>
          <v-form ref="formRef" @submit.prevent="saveRolePermission">
            <v-select
              v-model="form.role_id"
              :items="roles.filter(r => r.slug !== 'super-admin')"
              item-title="name"
              item-value="id"
              label="Role"
              variant="outlined"
              :rules="[v => !!v || 'Role is required']"
            />
            <v-select
              v-model="form.permission_id"
              :items="permissions"
              item-title="name"
              item-value="id"
              label="Permission"
              variant="outlined"
              :rules="[v => !!v || 'Permission is required']"
            />
          </v-form>
        </v-card-text>
        <v-card-actions class="pb-4 px-4">
          <v-spacer></v-spacer>
          <v-btn variant="text" class="bg-black" @click="closeDialog">Cancel</v-btn>
          <v-btn
            color="primary"
            variant="elevated"
            @click="saveRolePermission"
            :loading="saving"
          >
            Save
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

const rolePermissions = ref([])
const roles = ref([])
const permissions = ref([])
const selectedRole = ref(null)
const loading = ref(false)
const saving = ref(false)
const dialog = ref(false)
const formRef = ref(null)

const form = ref({
  role_id: null,
  permission_id: null
})

const headers = ref([
  { title: 'ID', key: 'id', sortable: true },
  { title: 'Role', key: 'role', sortable: false },
  { title: 'Permission', key: 'permission', sortable: false },
  { title: 'Actions', key: 'actions', sortable: false, align: 'center' }
])

const fetchRoles = async () => {
  try {
    const response = await axios.get('/api/roles')
    roles.value = response.data
  } catch (error) {
    console.error('Error fetching roles:', error)
  }
}

const fetchPermissions = async () => {
  try {
    const response = await axios.get('/api/permissions')
    permissions.value = response.data
  } catch (error) {
    console.error('Error fetching permissions:', error)
  }
}

const fetchRolePermissions = async () => {
  loading.value = true
  try {
    let url = '/api/role-permissions'
    if (selectedRole.value) {
      url = `/api/roles/${selectedRole.value}/permissions`
    }
    const response = await axios.get(url)
    rolePermissions.value = response.data
  } catch (error) {
    Swal.fire('Error', 'Failed to fetch role permissions', 'error')
  } finally {
    loading.value = false
  }
}

const saveRolePermission = async () => {
  const { valid } = await formRef.value.validate()
  if (!valid) return

  saving.value = true
  try {
    await axios.post('/api/role-permissions', form.value)
    Swal.fire({
      icon: 'success',
      title: 'Saved!',
      text: 'Role permission has been saved successfully.',
      timer: 2000,
      showConfirmButton: false
    })
    await fetchRolePermissions()
    closeDialog()
  } catch (error) {
    Swal.fire('Error', 'Failed to save role permission', 'error')
  } finally {
    saving.value = false
  }
}

const confirmDelete = (item) => {
  Swal.fire({
    title: 'Are you sure?',
    text: "You won't be able to revert this!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Yes, delete it!'
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        await axios.delete(`/api/role-permissions/${item.id}`)
        Swal.fire({
          icon: 'success',
          title: 'Deleted!',
          text: 'Role permission has been deleted.',
          timer: 1000,
          showConfirmButton: false
        })
        fetchRolePermissions()
      } catch (error) {
        Swal.fire('Error', 'Failed to delete role permission', 'error')
      }
    }
  })
}

const closeDialog = () => {
  dialog.value = false
  form.value = { role_id: null, permission_id: null }
  if (formRef.value) formRef.value.reset()
}

onMounted(() => {
  fetchRoles()
  fetchPermissions()
  fetchRolePermissions()
})
</script>
