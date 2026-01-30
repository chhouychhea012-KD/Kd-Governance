<template>
  <AuthenticatedLayout>
    <Head title="Roles" />
    
    <v-row class="mb-4">
      <v-col cols="12" md="8">
        <h1 class="text-h4 font-weight-bold">Roles</h1>
        <p class="text-subtitle-1 text-medium-emphasis">Manage user roles and their hierarchy levels</p>
      </v-col>
      <v-col cols="12" md="4" class="text-md-right">
        <v-btn
          v-if="auth.can('create-role')"
          class="bg-primary px-6"
          prepend-icon="mdi-plus"
          rounded="lg"
          @click="openCreateModal"
        >
          Create Role
        </v-btn>
      </v-col>
    </v-row>

    <v-row>
      <v-col cols="12">
        <v-card border flat class="rounded-lg">
          <v-card-title class="pa-4">
            <v-row align="center">
              <v-col cols="12" md="4">
                <v-text-field
                  v-model="search"
                  prepend-inner-icon="mdi-magnify"
                  label="Search roles"
                  single-line
                  hide-details
                  variant="outlined"
                  density="compact"
                  rounded="lg"
                ></v-text-field>
              </v-col>
            </v-row>
          </v-card-title>
          
          <v-divider></v-divider>
          
          <v-data-table
            :headers="headers"
            :items="Array.isArray(roles) ? roles : []"
            :search="search"
            :loading="loading"
            hover
          >
            <template v-slot:item.name="{ item }">
              <span>{{ item.name }}</span>
            </template>
            
            <template v-slot:item.level="{ item }">
              <v-chip size="small" :color="item.level <= 1 ? 'error' : 'primary'" variant="flat">
                Lvl {{ item.level }}
              </v-chip>
            </template>

            <template v-slot:item.actions="{ item }">
              <div class="d-flex justify-center">
                <v-tooltip text="Manage Permissions" location="top">
                  <template v-slot:activator="{ props }">
                    <v-btn
                      v-if="auth.can('read-role-permission') && item.slug !== 'super-admin'"
                      v-bind="props"
                      icon="mdi-shield-key"
                      size="x-small"
                      color="info"
                      variant="tonal"
                      class="me-2"
                      @click="managePermissions(item)"
                    ></v-btn>
                  </template>
                </v-tooltip>

                <v-btn
                  v-if="auth.can('update-role') && item.slug !== 'super-admin'"
                  icon="mdi-pencil"
                  size="x-small"
                  color="primary"
                  variant="tonal"
                  class="me-2"
                  @click="editRole(item)"
                ></v-btn>

                <v-btn
                  v-if="auth.can('delete-role') && item.slug !== 'super-admin'"
                  icon="mdi-delete"
                  size="x-small"
                  color="error"
                  variant="tonal"
                  @click="confirmDelete(item)"
                ></v-btn>
              </div>
            </template>
          </v-data-table>
        </v-card>
      </v-col>
    </v-row>

    <v-dialog v-model="dialog" max-width="500px" persistent>
      <v-card rounded="lg">
        <v-card-title class="text-h5 pa-4">
          {{ isEditing ? 'Edit Role' : 'Create Role' }}
        </v-card-title>
        <v-divider></v-divider>
        <v-card-text class="pa-4">
          <v-form ref="formRef" @submit.prevent="saveRole">
            <v-text-field
              v-model="form.name"
              label="Role Name"
              :rules="[v => !!v || 'Name is required']"
              required
              variant="outlined"
              rounded="lg"
              class="mb-2"
              prepend-inner-icon="mdi-shield-account"
            ></v-text-field>

            <v-text-field
              v-model.number="form.level"
              label="Hierarchy Level"
              type="number"
              :rules="[v => v >= 0 || 'Level must be a positive number']"
              required
              variant="outlined"
              rounded="lg"
              prepend-inner-icon="mdi-numeric"
              hint="Lower numbers indicate higher authority"
              persistent-hint
            ></v-text-field>

            <v-text-field
              v-model="form.slug"
              label="Slug"
              :rules="[
                v => !!v || 'Slug is required',
                v => /^[a-z0-9-]+$/ .test(v) || 'Slug must be lowercase letters, numbers, and hyphens only'
              ]"
              required
              variant="outlined"
              rounded="lg"
              prepend-inner-icon="mdi-tag"
              hint="Auto-generated from name"
              persistent-hint
            ></v-text-field>
          </v-form>
        </v-card-text>
        <v-divider></v-divider>
        <v-card-actions class="pa-4">
          <v-spacer></v-spacer>
          <v-btn variant="text" class="bg-black" rounded="lg" @click="closeDialog">Cancel</v-btn>
          <v-btn color="primary" variant="elevated" rounded="lg" class="px-6" @click="saveRole" :loading="saving">
            {{ isEditing ? 'Update Role' : 'Create Role' }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-dialog v-model="permissionsDialog" max-width="900px" scrollable persistent>
      <v-card rounded="lg">
        <v-toolbar color="primary" flat>
          <v-toolbar-title class="text-white">
            <v-icon start icon="mdi-shield-lock"></v-icon>
            Set Permissions: {{ selectedRole?.name }}
          </v-toolbar-title>
          <v-spacer></v-spacer>
          <v-btn icon="mdi-close" color="white" variant="text" @click="closePermissionsDialog"></v-btn>
        </v-toolbar>

        <div class="pa-4 bg-grey-lighten-4 border-b">
          <v-text-field
            v-model="permSearch"
            placeholder="Search permissions by name or module..."
            variant="outlined"
            density="compact"
            hide-details
            prepend-inner-icon="mdi-magnify"
            rounded="lg"
            bg-color="white"
          ></v-text-field>
        </div>

        <v-card-text class="pa-4 bg-grey-lighten-5">
          <div v-if="loadingPermissions" class="d-flex flex-column align-center pa-10">
            <v-progress-circular indeterminate color="primary" class="mb-2"></v-progress-circular>
            <span class="text-caption">Syncing data...</span>
          </div>

          <template v-else>
            <!-- Super Admin Info -->
            <v-alert
              v-if="selectedRole?.slug === 'super-admin'"
              type="info"
              variant="tonal"
              class="mb-4"
              icon="mdi-shield-check"
            >
              <strong>Super Admin Role</strong> automatically has all permissions. Permissions cannot be modified.
            </v-alert>

            <!-- Global Check/Uncheck All -->
            <div class="mb-4 d-flex justify-center">
              <v-btn
                v-if="selectedRole?.slug !== 'super-admin'"
                :color="assignedPermissionIds.length === allPermissions.length ? 'error' : 'primary'"
                variant="flat"
                rounded="lg"
                class="px-6"
                @click="toggleAllPermissions"
              >
                <v-icon start>
                  {{ assignedPermissionIds.length === allPermissions.length ? 'mdi-checkbox-blank-outline' : 'mdi-check-all' }}
                </v-icon>
                {{ assignedPermissionIds.length === allPermissions.length ? 'Uncheck All Permissions' : 'Check All Permissions' }}
              </v-btn>
            </div>

            <v-row>
            <v-col 
              v-for="(perms, groupName) in groupedPermissions" 
              :key="groupName" 
              cols="12" 
              md="6"
            >
              <v-card border flat class="rounded-lg fill-height">
                <v-card-title class="bg-grey-lighten-3 py-2 d-flex align-center">
                  <v-icon size="small" color="primary" class="me-2">mdi-folder-outline</v-icon>
                  <span class="text-subtitle-2 text-uppercase">{{ groupName }}</span>
                  <v-spacer></v-spacer>
                  <v-btn
                    v-if="selectedRole?.slug !== 'super-admin'"
                    size="x-small"
                    variant="flat"
                    :color="getGroupStatus(perms) === 'all' ? 'error' : 'primary'"
                    rounded="pill"
                    class="px-3"
                    @click="toggleGroup(perms)"
                  >
                    {{ getGroupStatus(perms) === 'all' ? 'Uncheck All' : 'Check All' }}
                  </v-btn>
                </v-card-title>
                <v-divider></v-divider>
                <v-card-text class="pa-2">
                  <v-list density="compact" class="bg-transparent">
                    <v-list-item
                      v-for="permission in perms"
                      :key="permission.id"
                      class="px-2"
                    >
                      <v-checkbox
                        :model-value="isAssigned(permission.id)"
                        :disabled="selectedRole?.slug === 'super-admin'"
                        density="compact"
                        hide-details
                        color="primary"
                        class="mt-0"
                        @update:model-value="togglePermission(permission)"
                      >
                        <template #label>
                          <div>
                            <div class="text-body-2">{{ permission.name }}</div>
                            <div class="text-caption text-medium-emphasis">{{ permission.slug }}</div>
                          </div>
                        </template>
                      </v-checkbox>
                    </v-list-item>
                  </v-list>
                </v-card-text>
              </v-card>
            </v-col>
            </v-row>
          </template>
        </v-card-text>

        <v-divider></v-divider>
        <v-card-actions class="pa-4">
          <v-chip variant="tonal" color="primary" size="small">
            Active Permissions: {{ assignedPermissionIds.length }}
          </v-chip>
          <v-spacer></v-spacer>
          <v-btn 
            color="primary" 
            variant="elevated" 
            rounded="lg" 
            class="px-8" 
            @click="closePermissionsDialog"
          >
            Done
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/layout/AuthenticatedLayout.vue'
import axios from 'axios'
import Swal from 'sweetalert2'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()

const roles = ref([])
const search = ref('')
const dialog = ref(false)
const loading = ref(false)
const saving = ref(false)
const isEditing = ref(false)
const formRef = ref(null)

const permissionsDialog = ref(false)
const loadingPermissions = ref(false)
const selectedRole = ref(null)

const allPermissions = ref([])
const rolePermissions = ref([])
const assignedPermissionIds = ref([])
const permSearch = ref('')
const form = ref({ id: null, name: '', slug: '', level: 0 })

// Watch for name changes to auto-generate slug
watch(() => form.value.name, (newName) => {
  if (newName && !isEditing.value) {
    form.value.slug = newName
      .toLowerCase()
      .replace(/[^a-z0-9\s-]/g, '') // Remove special characters
      .replace(/\s+/g, '-') // Replace spaces with hyphens
      .replace(/-+/g, '-') // Replace multiple hyphens with single
      .replace(/^-|-$/g, '') // Remove leading/trailing hyphens
  }
})

const headers = [
  { title: 'ID', key: 'id', width: 80 },
  { title: 'Role Name', key: 'name' },
  { title: 'Hierarchy Level', key: 'level' },
  { title: 'Actions', key: 'actions', sortable: false, align: 'center' }
]

const groupedPermissions = computed(() => {
  const filtered = allPermissions.value.filter(p =>
    p.name.toLowerCase().includes(permSearch.value.toLowerCase()) ||
    p.slug.toLowerCase().includes(permSearch.value.toLowerCase())
  )

  return filtered.reduce((groups, p) => {
    const group = p.slug.split('-')[1] || 'General'
    if (!groups[group]) groups[group] = []
    groups[group].push(p)
    return groups
  }, {})
})

const isAssigned = (permissionId) => assignedPermissionIds.value.includes(permissionId)

const toggleAllPermissions = async () => {
  const allAssigned = assignedPermissionIds.value.length === allPermissions.value.length
  loadingPermissions.value = true

  try {
    if (allAssigned) {
      // Uncheck all
      const toRemove = rolePermissions.value.filter(rp => rp.role_id === selectedRole.value.id)
      await Promise.all(toRemove.map(rp => axios.delete(`/api/role-permissions/${rp.id}`)))
      rolePermissions.value = rolePermissions.value.filter(rp => rp.role_id !== selectedRole.value.id)
      assignedPermissionIds.value = []
    } else {
      // Check all
      const toAdd = allPermissions.value.filter(p => !isAssigned(p.id))
      const results = await Promise.all(toAdd.map(p => axios.post('/api/role-permissions', {
        role_id: selectedRole.value.id,
        permission_id: p.id
      })))
      rolePermissions.value.push(...results.map(r => r.data))
      assignedPermissionIds.value = allPermissions.value.map(p => p.id)
    }
  } catch (error) {
    Swal.fire('Error', 'Bulk operation failed.', 'error')
    await fetchRolePermissions()
  } finally {
    loadingPermissions.value = false
  }
}

const getGroupStatus = (perms) => {
  const count = perms.filter(p => isAssigned(p.id)).length
  if (count === 0) return 'none'
  if (count === perms.length) return 'all'
  return 'some'
}

const togglePermission = async (permission) => {
  const pId = permission.id
  const currentlyHasIt = isAssigned(pId)

  if (currentlyHasIt && !auth.can('delete-role-permission')) {
    Swal.fire('Error', 'Unauthorized', 'error')
    return
  }
  if (!currentlyHasIt && !auth.can('create-role-permission')) {
    Swal.fire('Error', 'Unauthorized', 'error')
    return
  }

  try {
    if (currentlyHasIt) {
      const record = rolePermissions.value.find(
        rp => rp.role_id === selectedRole.value.id && rp.permission_id === pId
      )
      if (record) {
        await axios.delete(`/api/role-permissions/${record.id}`)
        rolePermissions.value = rolePermissions.value.filter(rp => rp.id !== record.id)
        assignedPermissionIds.value = assignedPermissionIds.value.filter(id => id !== pId)
      }
    } else {
      const response = await axios.post('/api/role-permissions', {
        role_id: selectedRole.value.id,
        permission_id: pId
      })
      rolePermissions.value.push(response.data)
      assignedPermissionIds.value.push(pId)
    }
  } catch (error) {
    console.error(error)
    Swal.fire('Sync Error', 'Database update failed. Reverting...', 'error')
    await fetchRolePermissions()
  }
}

const toggleGroup = async (perms) => {
  const status = getGroupStatus(perms)
  // loadingPermissions.value = true

  try {
    if (status === 'all') {
      const pIds = perms.map(p => p.id)
      const toDelete = rolePermissions.value.filter(rp => 
        rp.role_id === selectedRole.value.id && pIds.includes(rp.permission_id)
      )
      await Promise.all(toDelete.map(r => axios.delete(`/api/role-permissions/${r.id}`)))
      
      rolePermissions.value = rolePermissions.value.filter(rp => 
        !(rp.role_id === selectedRole.value.id && pIds.includes(rp.permission_id))
      )
      assignedPermissionIds.value = assignedPermissionIds.value.filter(id => !pIds.includes(id))
    } else {
      const toAdd = perms.filter(p => !isAssigned(p.id))
      const results = await Promise.all(toAdd.map(p => axios.post('/api/role-permissions', {
        role_id: selectedRole.value.id,
        permission_id: p.id
      })))
      
      results.forEach(res => {
        rolePermissions.value.push(res.data)
        assignedPermissionIds.value.push(res.data.permission_id)
      })
    }
  } catch (error) {
    Swal.fire('Error', 'Bulk update failed.', 'error')
    await fetchRolePermissions()
  }
   finally {
    // loadingPermissions.value = false
  }
}

const fetchRoles = async () => {
  loading.value = true
  try {
    const res = await axios.get('/api/roles')
    roles.value = res.data.data || res.data
  } finally {
    loading.value = false
  }
}

const fetchPermissions = async () => {
  const res = await axios.get('/api/permissions')
  allPermissions.value = res.data.data || res.data
}

const fetchRolePermissions = async () => {
  const res = await axios.get('/api/role-permissions')
  rolePermissions.value = res.data.data || res.data
  if (selectedRole.value) {
    assignedPermissionIds.value = rolePermissions.value
      .filter(rp => rp.role_id === selectedRole.value.id)
      .map(rp => rp.permission_id)
  }
}

const managePermissions = async (role) => {
  selectedRole.value = role
  permissionsDialog.value = true
  loadingPermissions.value = true
  await Promise.all([fetchPermissions(), fetchRolePermissions()])
  loadingPermissions.value = false
}

const closePermissionsDialog = () => {
  permissionsDialog.value = false
  selectedRole.value = null
}

const openCreateModal = () => {
  isEditing.value = false
  form.value = { id: null, name: '', slug: '', level: 0 }
  dialog.value = true
}

const editRole = (role) => {
  isEditing.value = true
  form.value = { ...role }
  dialog.value = true
}

const closeDialog = () => {
  dialog.value = false
  if (formRef.value) formRef.value.reset()
}

const saveRole = async () => {
  const { valid } = await formRef.value.validate()
  if (!valid) return
  saving.value = true
  try {
    if (isEditing.value) await axios.put(`/api/roles/${form.value.id}`, form.value)
    else await axios.post('/api/roles', form.value)
    await fetchRoles()
    closeDialog()
  } finally {
    saving.value = false
  }
}

const confirmDelete = (item) => {
  Swal.fire({
    title: 'Delete Role?',
    text: 'This action cannot be undone.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33'
  }).then(async r => {
    if (r.isConfirmed) {
      await axios.delete(`/api/roles/${item.id}`)
      fetchRoles()
    }
  })
}

onMounted(fetchRoles)
</script>