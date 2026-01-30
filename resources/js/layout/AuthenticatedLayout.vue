<template>
  <v-app v-if="auth.isInitialized">
    <v-app-bar 
      flat 
      border="b"
      class="px-4"
      color="surface"
      elevation="0"
    >
      <v-app-bar-nav-icon 
        variant="text" 
        @click="drawer = !drawer"
      ></v-app-bar-nav-icon>

      <v-toolbar-title class="ml-0">
        <Link href="/dashboard" class="d-flex align-center text-decoration-none">
           <img src="/logo.png" width="32" height="32" alt="Logo" class="rounded">
          <span class="ms-3 text-h6 font-weight-black letter-spacing-tight text-high-emphasis">
            KD Governance
          </span>
        </Link>
      </v-toolbar-title>

      <v-spacer></v-spacer>

      <div class="d-flex align-center">
        <v-btn icon="mdi-magnify" variant="text" size="small" class="me-1"></v-btn>
        <v-btn icon="mdi-bell-outline" variant="text" size="small" class="me-2">
          <v-badge color="error" dot>
            <v-icon>mdi-bell-outline</v-icon>
          </v-badge>
        </v-btn>
        
        <v-menu location="bottom end" transition="slide-y-transition">
          <template #activator="{ props }">
            <v-avatar 
              v-bind="props" 
              color="primary" 
              class="cursor-pointer elevation-1"
              size="36"
            >
              <span class="text-caption font-weight-bold text-white" v-if="!$page.props.auth_user?.profile_image">
                {{ getUserInitials($page.props.auth?.user?.name) }}
                
              </span>
              <img v-else :src="'/images/profile/'+$page.props.auth_user?.profile_image" alt="">
            </v-avatar>
          </template>
          
          <v-card min-width="240" class="mt-2 border shadow-sm">
            <v-list lines="two" class="pa-2">
              <v-list-item
                :title="$page.props.auth?.user?.name"
                :subtitle="$page.props.auth?.user?.email"
              >
                <template #prepend>
                  <v-avatar color="primary" size="40">
                    <!-- {{ getUserInitials($page.props.auth?.user?.name) }} -->
                      <span class="text-caption font-weight-bold text-white" v-if="!$page.props.auth_user?.profile_image">
                        {{ getUserInitials($page.props.auth?.user?.name) }}
                        
                      </span>
                      <img v-else :src="'/images/profile/'+$page.props.auth_user?.profile_image" alt="">
                  </v-avatar>
                </template>
              </v-list-item>
            </v-list>
            
            <v-divider></v-divider>
            
            <v-list density="compact" nav class="pa-2">
              <v-list-item 
                prepend-icon="mdi-account-outline" 
                title="My Profile" 
                @click="router.visit('/profile')"
              ></v-list-item>
              <v-list-item 
                prepend-icon="mdi-cog-outline" 
                title="Settings"
                @click="router.visit('/settings')"
              ></v-list-item>
              
              <v-divider class="my-2"></v-divider>
              
              <v-list-item 
                base-color="error"
                prepend-icon="mdi-logout-variant" 
                title="Logout"
                @click="logout"
              ></v-list-item>
            </v-list>
          </v-card>
        </v-menu>
      </div>
    </v-app-bar>

    <v-navigation-drawer
      v-model="drawer"
      elevation="0"
      border="e"
      width="280"
      class="sidebar-drawer"
    >
      <div class="pa-4">
        <v-list-item class="bg-grey-lighten-4 rounded-lg border">
          <template #prepend>
            <v-avatar size="32" color="secondary" rounded="lg">
              <v-icon icon="mdi-office-building-marker" color="white" size="20"></v-icon>
            </v-avatar>
          </template>
          <v-list-item-title class="font-weight-bold text-body-2">KD Capital</v-list-item-title>
          <v-list-item-subtitle class="text-caption">HQ Office</v-list-item-subtitle>
          <template #append>
            <v-icon icon="mdi-chevron-up-down" size="small" class="text-medium-emphasis"></v-icon>
          </template>
        </v-list-item>
      </div>

      <v-list nav density="compact" class="px-4 nav-list">
        <template v-for="(group, gIdx) in groupedMenu" :key="gIdx">
          <div class="nav-header text-overline text-medium-emphasis ps-3 mb-2 mt-4">
            {{ group.header }}
          </div>
          
          <v-list-item
            v-for="item in group.items"
            :key="item.slug"
            v-show="canAccess(item.permission)"
            :active="isActiveRoute(item.route)"
            rounded="lg"
            class="mb-1 py-1"
            color="primary"
            @click="router.visit(item.route)"
          >
            <template #prepend>
              <v-icon :icon="item.icon" size="20" class="me-3"></v-icon>
            </template>
            <v-list-item-title class="font-weight-medium">{{ item.name }}</v-list-item-title>
          </v-list-item>
        </template>
      </v-list>

      <!-- <template #append>
        <div class="pa-4">
          <v-card color="primary-lighten-5" flat class="rounded-lg pa-3 border-dashed border-primary">
            <div class="text-caption font-weight-bold text-primary mb-1">Need help?</div>
            <div class="text-caption text-medium-emphasis mb-2">Check our documentation for quick guides.</div>
            <v-btn block size="x-small" color="primary" variant="flat">Docs</v-btn>
          </v-card>
        </div>
      </template> -->
    </v-navigation-drawer>

    <v-main class="bg-grey-lighten-5">
      <v-container fluid class="pa-4 pt-0 mt-3" style="min-height: calc(100vh - 48px)">
        <v-row>
          <v-col cols="12">
            <slot />
          </v-col>
        </v-row>
      </v-container>
    </v-main>

    <v-footer 
      app 
      height="48" 
      class="bg-white border-t px-6 text-caption d-flex justify-space-between align-center"
    >
      <div class="text-medium-emphasis">
        &copy; {{ currentYear }} <span class="font-weight-bold">KD Digitech</span>. All rights reserved.
      </div>
      <div class="d-flex align-center">
        <span class="me-4 text-medium-emphasis">v1.0.0</span>
        <v-chip size="x-small" color="success" variant="tonal" class="font-weight-bold">
          <v-icon start icon="mdi-circle" size="8"></v-icon>
          System Online
        </v-chip>
      </div>
    </v-footer>
  </v-app>
  <v-app v-else>
  <div class="d-flex align-center justify-center fill-height bg-grey-lighten-5">
    <div class="text-center">
      <v-progress-circular indeterminate color="primary" size="64" width="6" />
      <div class="mt-4 text-body-1 font-weight-medium text-medium-emphasis">
        Loading system configuration...
      </div>
    </div>
  </div>
</v-app>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'
import { useAuthStore } from '@/stores/auth'

const drawer = ref(true)
const page = usePage()
const currentYear = new Date().getFullYear()
const auth = useAuthStore()

onMounted(async () => {
  // if (!auth.isInitialized) {
    await auth.initializeFromToken()
  // }
})

const menuItems = [
  { name: 'Dashboard', slug: 'dashboard', route: '/dashboard', icon: 'mdi-view-dashboard-outline', permission: 'read-dashboard', group: 'Overview' },
  { name: 'Teams', slug: 'teams', route: '/teams', icon: 'mdi-account-group-outline', permission: 'read-teams', group: 'Management' },
  { name: 'Users', slug: 'users', route: '/users', icon: 'mdi-account-outline', permission: 'read-user', group: 'Management' },
  { name: 'Roles', slug: 'roles', route: '/roles', icon: 'mdi-shield-account-outline', permission: 'read-role', group: 'Management' },
  { name: 'Permissions', slug: 'permissions', route: '/permissions', icon: 'mdi-key-outline', permission: 'read-permission', group: 'Management' },
  { name: 'Settings', slug: 'settings', route: '/settings', icon: 'mdi-cog-outline', permission: 'read-settings', group: 'Management' }
]

const groupedMenu = computed(() => {
  const groups = ['Overview', 'Management', 'Evaluation']
  return groups.map(header => ({
    header,
    items: menuItems.filter(i => i.group === header)
  }))
})

const logout = () => {
  auth.logout()
  router.post('/logout')
}

const getUserInitials = (name) => {
  if (!name) return 'U'
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
}

const isActiveRoute = (route) => {
  return page.url === route || page.url.startsWith(route + '/')
}

const canAccess = (permission) => {
  if (!permission) return true
  if (auth.isSuperAdmin) return true
  return auth.can(permission)
}
</script>

<style scoped>
.letter-spacing-tight {
  letter-spacing: -0.025em !important;
}

.cursor-pointer {
  cursor: pointer;
}

.nav-header {
  font-size: 0.65rem !important;
  font-weight: 700 !important;
  letter-spacing: 0.05em !important;
}

.sidebar-drawer {
  background-color: #ffffff !important;
}

:deep(.v-list-item--active) {
  background-color: rgb(var(--v-theme-primary), 0.08) !important;
  color: rgb(var(--v-theme-primary)) !important;
}

:deep(.v-list-item--active .v-icon) {
  color: rgb(var(--v-theme-primary)) !important;
}

:deep(.v-navigation-drawer__border) {
  background-color: #e5e7eb !important;
}

.border-dashed {
  border: 1px dashed !important;
}

.primary-lighten-5 {
  background-color: #f0f7ff !important;
}
</style>
