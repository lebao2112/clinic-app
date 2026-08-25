<template>
  <el-container class="layout-container">
    <!-- Sidebar Navigation -->
    <el-aside :width="sidebarCollapsed ? '76px' : '260px'" class="aside-menu" :class="{ 'is-collapsed': sidebarCollapsed }">
      <div class="sidebar-logo">
        <div class="brand-mark">
          <span class="logo-dot"></span>
          <span class="logo-text">ClinicHub</span>
        </div>

        <el-button
          class="sidebar-toggle"
          link
          @click="sidebarCollapsed = !sidebarCollapsed"
          :title="sidebarCollapsed ? 'Mở rộng menu' : 'Thu gọn menu'"
        >
          <el-icon>
            <Expand v-if="sidebarCollapsed" />
            <Fold v-else />
          </el-icon>
        </el-button>
      </div>
      <el-menu
        router
        :collapse="sidebarCollapsed"
        :default-active="$route.path"
        class="custom-menu"
      >
        <div class="menu-section-title">Tổng quan</div>

        <el-menu-item index="/dashboard" v-if="hasAccess(['ADMIN', 'RECEPTIONIST', 'DOCTOR', 'PHARMACIST', 'CASHIER'])">
          <el-icon><Odometer /></el-icon>
          <span>Dashboard</span>
        </el-menu-item>

        <div class="menu-section-title">Khám chữa bệnh</div>

        <el-menu-item index="/patients" v-if="hasAccess(['ADMIN', 'RECEPTIONIST', 'DOCTOR', 'CASHIER'])">
          <el-icon><User /></el-icon>
          <span>Bệnh nhân</span>
        </el-menu-item>
        
        <el-menu-item index="/doctors" v-if="hasAccess(['ADMIN', 'RECEPTIONIST'])">
          <el-icon><Avatar /></el-icon>
          <span>Bác sĩ</span>
        </el-menu-item>
        
        <el-menu-item index="/appointments" v-if="hasAccess(['ADMIN', 'RECEPTIONIST', 'DOCTOR', 'CASHIER'])">
          <el-icon><Calendar /></el-icon>
          <span>Lịch hẹn</span>
        </el-menu-item>

        <el-menu-item index="/examinations" v-if="hasAccess(['ADMIN', 'DOCTOR'])">
          <el-icon><Files /></el-icon>
          <span>Phiếu khám</span>
        </el-menu-item>

        <div class="menu-section-title">Dược & thanh toán</div>

        <el-menu-item index="/specialties" v-if="hasAccess(['ADMIN', 'RECEPTIONIST'])">
          <el-icon><FirstAidKit /></el-icon>
          <span>Chuyên khoa</span>
        </el-menu-item>

        <el-menu-item index="/medicines" v-if="hasAccess(['ADMIN', 'PHARMACIST', 'DOCTOR'])">
          <el-icon><Box /></el-icon>
          <span>Thuốc</span>
        </el-menu-item>

        <el-menu-item index="/prescriptions" v-if="hasAccess(['ADMIN', 'DOCTOR', 'PHARMACIST'])">
          <el-icon><Notebook /></el-icon>
          <span>Đơn thuốc</span>
        </el-menu-item>
        
        <el-menu-item index="/invoices" v-if="hasAccess(['ADMIN', 'CASHIER'])">
          <el-icon><Document /></el-icon>
          <span>Hóa đơn</span>
        </el-menu-item>

        <div class="menu-section-title">Hệ thống</div>

        <el-menu-item index="/users" v-if="hasAccess(['ADMIN'])">
          <el-icon><UserFilled /></el-icon>
          <span>Người dùng</span>
        </el-menu-item>
      </el-menu>
    </el-aside>

    <!-- Main Container -->
    <el-container class="main-container">
      <el-header class="header-bar">
        
        <!-- Global Search Box -->
        <div class="header-left">
          <div class="search-box">
            <el-icon><Search /></el-icon>
            <input 
              ref="searchInputRef"
              v-model="globalSearchQuery"
              type="text" 
              placeholder="Search..." 
              @keyup.enter="handleGlobalSearch"
            />
            <span class="shortcut">⌘K</span>
          </div>
        </div>
        
        <!-- Header Actions -->
        <div class="header-right">
          <el-button class="ai-btn" type="primary" size="small">
            <el-icon><MagicStick /></el-icon> AI Assistance
          </el-button>
          
          <el-dropdown trigger="click" @command="(path) => router.push(path)">
            <div class="header-icon-btn" title="Thêm mới">
              <el-icon><Plus /></el-icon>
            </div>
            <template #dropdown>
              <el-dropdown-menu>
                <el-dropdown-item command="/patients" v-if="hasAccess(['ADMIN', 'RECEPTIONIST'])">
                  <el-icon><User /></el-icon> Thêm Bệnh nhân
                </el-dropdown-item>
                <el-dropdown-item command="/appointments" v-if="hasAccess(['ADMIN', 'RECEPTIONIST'])">
                  <el-icon><Calendar /></el-icon> Lập Lịch hẹn
                </el-dropdown-item>
                <el-dropdown-item command="/examinations" v-if="hasAccess(['ADMIN', 'DOCTOR'])">
                  <el-icon><Files /></el-icon> Lập Phiếu khám
                </el-dropdown-item>
              </el-dropdown-menu>
            </template>
          </el-dropdown>

          <div class="header-icon-btn" title="Lịch trình" @click="router.push('/appointments')">
            <el-icon><Calendar /></el-icon>
          </div>
          
          <div class="header-icon-btn" title="Thông báo">
            <el-icon><Bell /></el-icon>
          </div>

          <!-- User Profile Dropdown -->
          <el-dropdown trigger="click">
            <div class="user-dropdown-link">
              <el-avatar :size="36" class="user-avatar">{{ getAvatarLetter(currentUser.name) }}</el-avatar>
              <div class="user-info-text">
                <span class="user-name">{{ currentUser.name || 'Người dùng' }}</span>
                <span class="user-role">{{ formatRole(currentUser.role) }}</span>
              </div>
              <el-icon class="el-icon--right"><ArrowDown /></el-icon>
            </div>
            <template #dropdown>
              <el-dropdown-menu>
                <el-dropdown-item @click="openProfileDialog">Thông tin cá nhân</el-dropdown-item>
                <el-dropdown-item divided @click="handleLogout" style="color: #ef4444;">Đăng xuất</el-dropdown-item>
              </el-dropdown-menu>
            </template>
          </el-dropdown>
        </div>
      </el-header>

      <!-- Main Content Area -->
      <el-main class="main-content">
        <div class="content-wrapper">
          <router-view></router-view>
        </div>
      </el-main>
    </el-container>

    <!-- ================= PROFILE & SECURITY DIALOG ================= -->
    <el-dialog
      v-model="profileDialogVisible"
      title="Thông tin tài khoản cá nhân"
      width="820px"
      destroy-on-close
      class="custom-profile-dialog"
    >
      <div class="profile-dialog-body">

        <!-- Left account summary -->
        <aside class="profile-sidebar">
          <div class="dialog-avatar-container">
            <el-avatar :size="78" class="dialog-large-avatar">
              {{ getAvatarLetter(profileForm.name) }}
            </el-avatar>
            <span class="dialog-avatar-title">{{ profileForm.name || 'Người dùng' }}</span>
            <span class="dialog-avatar-subtitle">{{ formatRole(currentUser.role) }}</span>
          </div>

          <div class="account-summary-card">
            <div class="meta-item">
              <span class="meta-icon"><el-icon><Message /></el-icon></span>
              <div class="meta-content">
                <div class="meta-val">{{ currentUser.email || 'Chưa cập nhật' }}</div>
                <div class="meta-lbl">Email đăng nhập</div>
              </div>
            </div>

            <div class="meta-item">
              <span class="meta-icon"><el-icon><User /></el-icon></span>
              <div class="meta-content">
                <div class="meta-val">{{ formatRole(currentUser.role).toUpperCase() }}</div>
                <div class="meta-lbl">Vai trò</div>
              </div>
            </div>

            <div class="meta-item">
              <span class="meta-icon status-icon"><el-icon><CircleCheck /></el-icon></span>
              <div class="meta-content">
                <div class="meta-val status-active">
                  <span class="dot-active"></span>
                  Đang hoạt động
                </div>
                <div class="meta-lbl">Trạng thái tài khoản</div>
              </div>
            </div>

            <div class="meta-item">
              <span class="meta-icon"><el-icon><Calendar /></el-icon></span>
              <div class="meta-content">
                <div class="meta-val">{{ formatDateTime(currentUser.created_at) }}</div>
                <div class="meta-lbl">Ngày tạo tài khoản</div>
              </div>
            </div>
          </div>
        </aside>

        <!-- Right editable content -->
        <section class="profile-content">
          <el-tabs v-model="activeTab" class="profile-tabs">

            <el-tab-pane label="Thông tin cá nhân" name="info">
              <el-form :model="profileForm" label-position="top" class="profile-form">
                <el-form-item label="Họ tên">
                  <el-input
                    v-model="profileForm.name"
                    size="large"
                    placeholder="Nhập họ và tên"
                  />
                </el-form-item>

                <el-form-item label="Email đăng nhập">
                  <el-input
                    v-model="profileForm.email"
                    disabled
                    size="large"
                  />
                  <div class="form-tip">
                    Email đăng nhập không thể thay đổi. Vui lòng liên hệ quản trị hệ thống nếu cần cập nhật.
                  </div>
                </el-form-item>

                <el-form-item label="Vai trò">
                  <el-select
                    v-model="roleDisplay"
                    disabled
                    size="large"
                    style="width: 100%;"
                  >
                    <el-option
                      :label="formatRole(currentUser.role).toUpperCase()"
                      :value="currentUser.role"
                    />
                  </el-select>
                </el-form-item>
              </el-form>
            </el-tab-pane>

            <el-tab-pane label="Đổi mật khẩu" name="security">
              <el-form :model="profileForm" label-position="top" class="profile-form">

                <el-form-item label="Mật khẩu hiện tại">
                  <el-input
                    v-model="profileForm.currentPassword"
                    type="password"
                    placeholder="Nhập mật khẩu hiện tại"
                    size="large"
                    show-password
                  />
                </el-form-item>

                <el-form-item label="Mật khẩu mới">
                  <el-input
                    v-model="profileForm.password"
                    type="password"
                    placeholder="Nhập mật khẩu mới"
                    size="large"
                    show-password
                  />
                  <div class="password-rules">
                    <span :class="{ 'rule-valid': profileForm.password.length >= 8 }">
                      <el-icon><CircleCheck /></el-icon>
                      Ít nhất 8 ký tự
                    </span>
                    <span :class="{ 'rule-valid': /(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/.test(profileForm.password) }">
                      <el-icon><CircleCheck /></el-icon>
                      Bao gồm chữ hoa, chữ thường và số
                    </span>
                  </div>
                </el-form-item>

                <el-form-item label="Xác nhận mật khẩu mới">
                  <el-input
                    v-model="profileForm.passwordConfirmation"
                    type="password"
                    placeholder="Nhập lại mật khẩu mới"
                    size="large"
                    show-password
                  />
                  <div
                    v-if="profileForm.passwordConfirmation"
                    class="password-match"
                    :class="{ valid: profileForm.password === profileForm.passwordConfirmation }"
                  >
                    <el-icon>
                      <CircleCheck v-if="profileForm.password === profileForm.passwordConfirmation" />
                      <CircleClose v-else />
                    </el-icon>
                    {{ profileForm.password === profileForm.passwordConfirmation
                      ? 'Hai mật khẩu trùng khớp'
                      : 'Mật khẩu không khớp' }}
                  </div>
                </el-form-item>
              </el-form>
            </el-tab-pane>

          </el-tabs>
        </section>
      </div>

      <template #footer>
        <div class="dialog-footer">
          <el-button
            size="large"
            class="profile-cancel-btn"
            @click="profileDialogVisible = false"
          >
            Hủy bỏ
          </el-button>

          <el-button
            type="primary"
            size="large"
            class="profile-save-btn"
            :loading="profileSubmitting"
            @click="updateProfile"
          >
            {{ profileSubmitting ? 'Đang lưu...' : 'Lưu thay đổi' }}
          </el-button>
        </div>
      </template>
    </el-dialog>
  </el-container>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { 
  Odometer, User, Avatar, Calendar, Files, Document, ArrowDown, Fold, Expand, 
  Search, MagicStick, Plus, Bell, FirstAidKit, UserFilled,
  Box, Notebook, Message, CircleCheck, CircleClose, Clock 
} from '@element-plus/icons-vue';
import { ElMessage } from 'element-plus';

const router = useRouter();
const currentUser = ref({ id: null, name: '', email: '', role: 'ADMIN', created_at: null });
const searchInputRef = ref(null);
const globalSearchQuery = ref('');
const sidebarCollapsed = ref(false);

// --- PROFILE & SECURITY DIALOG STATE ---
const profileDialogVisible = ref(false);
const profileSubmitting = ref(false);
const activeTab = ref('info');
const profileForm = reactive({
  name: '',
  email: '',
  currentPassword: '',
  password: '',
  passwordConfirmation: ''
});

const roleDisplay = computed(() => formatRole(currentUser.value.role).toUpperCase());

/**
 * Format ISO database datetime into a clean readable string (DD/MM/YYYY HH:mm)
 */
const formatDateTime = (dateString) => {
  if (!dateString) return '---';
  const date = new Date(dateString);
  if (isNaN(date.getTime())) return dateString;
  
  const day = String(date.getDate()).padStart(2, '0');
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const year = date.getFullYear();
  const hours = String(date.getHours()).padStart(2, '0');
  const minutes = String(date.getMinutes()).padStart(2, '0');
  
  return `${day}/${month}/${year} ${hours}:${minutes}`;
};

const openProfileDialog = () => {
  profileForm.name = currentUser.value.name;
  profileForm.email = currentUser.value.email;
  profileForm.currentPassword = '';
  profileForm.password = '';
  profileForm.passwordConfirmation = '';
  activeTab.value = 'info';
  profileDialogVisible.value = true;
};

const updateProfile = async () => {
  if (!profileForm.name?.trim()) {
    ElMessage.error('Vui lòng nhập họ tên!');
    return;
  }

  if (activeTab.value === 'security') {
    if (!profileForm.currentPassword || !profileForm.password) {
      ElMessage.error('Vui lòng nhập đầy đủ thông tin đổi mật khẩu!');
      return;
    }

    if (profileForm.password.length < 8) {
      ElMessage.error('Mật khẩu mới phải có ít nhất 8 ký tự!');
      return;
    }

    if (!/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/.test(profileForm.password)) {
      ElMessage.error('Mật khẩu mới phải có chữ hoa, chữ thường và số!');
      return;
    }

    if (profileForm.password !== profileForm.passwordConfirmation) {
      ElMessage.error('Mật khẩu xác nhận không khớp!');
      return;
    }
  }

  profileSubmitting.value = true;
  try {
    const payload = { name: profileForm.name };
    if (activeTab.value === 'security' && profileForm.password) {
      payload.password = profileForm.password;
      payload.current_password = profileForm.currentPassword;
    }
    
    await axios.put(`/api/users/${currentUser.value.id}`, payload);
    
    currentUser.value.name = profileForm.name;
    ElMessage.success('Cập nhật thông tin tài khoản thành công!');
    profileDialogVisible.value = false;
  } catch (error) {
    ElMessage.error(error.response?.data?.message || 'Không thể cập nhật thông tin!');
  } finally {
    profileSubmitting.value = false;
  }
};

const handleGlobalSearch = () => {
  if (!globalSearchQuery.value.trim()) return;
  ElMessage.info(`Searching for: "${globalSearchQuery.value}"...`);
};

const handleShortcut = (e) => {
  if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
    e.preventDefault();
    searchInputRef.value?.focus();
  }
};

const fetchCurrentUser = async () => {
  try {
    const response = await axios.get('/api/me');
    currentUser.value = response.data.data || response.data;
  } catch (error) {
    console.error('Error fetching current user:', error);
  }
};

const hasAccess = (allowedRoles) => {
  if (!currentUser.value || !currentUser.value.role) return false;
  const userRole = currentUser.value.role.toUpperCase();
  if (userRole === 'ADMIN') return true;
  const normalizedAllowed = allowedRoles.map(r => r.toUpperCase());
  return normalizedAllowed.includes(userRole);
};

const getAvatarLetter = (name) => name ? name.charAt(0).toUpperCase() : 'U';

const formatRole = (role) => {
  if (!role) return 'Nhân viên';
  const roleMap = {
    'ADMIN': 'Quản trị viên',
    'RECEPTIONIST': 'Lễ tân',
    'DOCTOR': 'Bác sĩ',
    'PHARMACIST': 'Dược sĩ',
    'CASHIER': 'Thu ngân'
  };
  return roleMap[role.toUpperCase()] || 'Nhân viên';
};

const handleLogout = async () => {
  try {
    await axios.post('/api/logout');
  } catch (error) {
    console.error('API logout error:', error);
  } finally {
    localStorage.removeItem('auth_token');
    ElMessage.success('Đã đăng xuất thành công!');
    router.push('/login');
  }
};

onMounted(() => {
  fetchCurrentUser();
  window.addEventListener('keydown', handleShortcut);
});

onUnmounted(() => {
  window.removeEventListener('keydown', handleShortcut);
});
</script>

<style scoped>
.layout-container {
  height: 100vh;
  width: 100vw;
  display: flex;
  overflow: hidden;
  background-color: #f8fafc;
  font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
}
.aside-menu {
  height: 100vh;
  background-color: #ffffff !important;
  border-right: 1px solid #e2e8f0 !important;
  display: flex;
  flex-direction: column;
  z-index: 20;
  transition: width 0.25s ease;
  overflow: hidden;
}
.sidebar-logo {
  height: 72px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 14px 0 22px;
  border-bottom: 1px solid #f1f5f9;
  flex: 0 0 auto;
}
.brand-mark {
  min-width: 0;
  display: flex;
  align-items: center;
  gap: 10px;
}
.logo-dot {
  width: 12px;
  height: 12px;
  background: linear-gradient(135deg, #38bdf8, #0284c7);
  border-radius: 4px;
  flex: 0 0 auto;
}
.logo-text {
  font-size: 20px;
  font-weight: 800;
  color: #0f172a;
  letter-spacing: -0.5px;
  white-space: nowrap;
  transition: opacity 0.2s ease, width 0.2s ease;
}
.sidebar-toggle {
  width: 34px !important;
  height: 34px !important;
  border-radius: 9px !important;
  color: #64748b !important;
  flex: 0 0 auto;
}
.sidebar-toggle:hover {
  background: #eff6ff !important;
  color: #0284c7 !important;
}
.menu-section-title {
  padding: 18px 24px 7px 24px;
  font-size: 10px;
  font-weight: 800;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.9px;
  white-space: nowrap;
  transition: opacity 0.2s ease;
}
.custom-menu {
  border-right: none !important;
  background-color: transparent !important;
  padding: 0 12px 16px;
  overflow-y: auto;
  overflow-x: hidden;
}
.custom-menu::-webkit-scrollbar { width: 5px; }
.custom-menu::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
.custom-menu :deep(.el-menu-item) {
  height: 46px !important;
  line-height: 46px !important;
  border-radius: 11px !important;
  margin-bottom: 4px !important;
  color: #64748b !important;
  font-weight: 600;
  transition: all 0.2s ease !important;
  position: relative;
}
.custom-menu :deep(.el-menu-item:hover) {
  background-color: #f8fafc !important;
  color: #0284c7 !important;
}
.custom-menu :deep(.el-menu-item.is-active) {
  background: linear-gradient(90deg, #eff8ff 0%, #f4f9ff 100%) !important;
  color: #0284c7 !important;
  font-weight: 700;
}
.custom-menu :deep(.el-menu-item.is-active::before) {
  content: '';
  position: absolute;
  left: 0;
  top: 10px;
  bottom: 10px;
  width: 3px;
  border-radius: 0 6px 6px 0;
  background: linear-gradient(180deg, #38bdf8, #0284c7);
}
.custom-menu :deep(.el-menu-item .el-icon) { margin-right: 12px; font-size: 18px; color: #64748b; }
.custom-menu :deep(.el-menu-item.is-active .el-icon) { color: #0284c7 !important; }
.aside-menu.is-collapsed .logo-text { width: 0; opacity: 0; overflow: hidden; }
.aside-menu.is-collapsed .sidebar-logo { justify-content: center; padding: 0 8px; }
.aside-menu.is-collapsed .brand-mark { justify-content: center; }
.aside-menu.is-collapsed .menu-section-title { opacity: 0; height: 6px; padding: 0; overflow: hidden; }
.aside-menu.is-collapsed .custom-menu { padding-left: 10px; padding-right: 10px; }
.custom-menu.el-menu--collapse { width: 100% !important; }
.custom-menu.el-menu--collapse :deep(.el-menu-item) { justify-content: center; padding: 0 !important; }
.custom-menu.el-menu--collapse :deep(.el-menu-item .el-icon) { margin: 0 !important; }
.custom-menu.el-menu--collapse :deep(.el-menu-item.is-active::before) { left: 0; }
.main-container {
  flex: 1;
  display: flex;
  flex-direction: column;
  height: 100vh;
  overflow: hidden;
}
.header-bar {
  height: 72px !important;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 32px;
  background-color: #ffffff;
  border-bottom: 1px solid #e2e8f0;
  z-index: 10;
}
.search-box {
  display: flex;
  align-items: center;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  padding: 8px 16px;
  width: 300px;
  gap: 10px;
}
.search-box input {
  border: none;
  background: transparent;
  outline: none;
  font-size: 14px;
  width: 100%;
  color: #0f172a;
}
.shortcut {
  font-size: 11px;
  background: #e2e8f0;
  padding: 2px 6px;
  border-radius: 4px;
  color: #64748b;
  font-weight: 600;
}
.header-right {
  display: flex;
  align-items: center;
  gap: 16px;
}
.ai-btn {
  background: #0ea5e9 !important;
  border: none !important;
  border-radius: 8px !important;
  font-weight: 600 !important;
  padding: 8px 16px !important;
}
.header-icon-btn {
  width: 36px;
  height: 36px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: #64748b;
  transition: 0.2s;
}
.header-icon-btn:hover {
  background: #f1f5f9;
  color: #0f172a;
}
.user-dropdown-link {
  display: flex;
  align-items: center;
  gap: 12px;
  cursor: pointer;
  padding: 4px 8px;
  border-radius: 8px;
}
.user-avatar {
  background: #0284c7;
  color: white;
  font-weight: 700;
}
.user-info-text {
  display: flex;
  flex-direction: column;
  text-align: left;
}
.user-name {
  font-size: 13px;
  font-weight: 700;
  color: #0f172a;
  line-height: 1.2;
}
.user-role {
  font-size: 11px;
  color: #64748b;
  font-weight: 600;
}
.main-content {
  flex: 1;
  overflow-y: auto;
  padding: 32px;
  background-color: #f8fafc;
}

/* --- PROFILE DIALOG --- */
.custom-profile-dialog :deep(.el-dialog) {
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 24px 80px rgba(15, 23, 42, 0.18);
}

.custom-profile-dialog :deep(.el-dialog__header) {
  margin-right: 0;
  padding: 22px 28px 18px;
  border-bottom: 1px solid #eef2f7;
}

.custom-profile-dialog :deep(.el-dialog__title) {
  font-size: 20px;
  font-weight: 800;
  color: #0f172a;
}

.custom-profile-dialog :deep(.el-dialog__body) {
  padding: 24px 28px 18px;
}

.custom-profile-dialog :deep(.el-dialog__footer) {
  padding: 0 28px 24px;
}

.profile-dialog-body {
  display: grid;
  grid-template-columns: 245px minmax(0, 1fr);
  gap: 28px;
  min-height: 395px;
}

.profile-sidebar {
  padding-right: 24px;
  border-right: 1px solid #eef2f7;
}

.dialog-avatar-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  margin-bottom: 18px;
}

.dialog-large-avatar {
  background: linear-gradient(135deg, #0ea5e9, #0284c7);
  color: #fff;
  font-size: 28px;
  font-weight: 800;
  box-shadow: 0 10px 24px rgba(14, 165, 233, 0.22);
  margin-bottom: 10px;
}

.dialog-avatar-title {
  font-size: 16px;
  font-weight: 800;
  color: #0f172a;
}

.dialog-avatar-subtitle {
  margin-top: 4px;
  font-size: 12px;
  color: #64748b;
  font-weight: 600;
}

.account-summary-card {
  padding: 16px;
  border: 1px solid #e8eef5;
  background: #fbfdff;
  border-radius: 14px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.meta-item {
  display: flex;
  align-items: flex-start;
  gap: 10px;
}

.meta-icon {
  width: 28px;
  height: 28px;
  border-radius: 9px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  flex: 0 0 auto;
  color: #0284c7;
  background: #eff8ff;
}

.status-icon {
  color: #16a34a;
  background: #effbf2;
}

.meta-content {
  min-width: 0;
}

.meta-val {
  color: #0f172a;
  font-size: 12px;
  font-weight: 700;
  line-height: 1.35;
  overflow-wrap: anywhere;
}

.meta-lbl {
  margin-top: 2px;
  color: #94a3b8;
  font-size: 11px;
}

.status-active {
  display: flex;
  align-items: center;
  gap: 6px;
  color: #16a34a !important;
}

.dot-active {
  width: 7px;
  height: 7px;
  display: inline-block;
  border-radius: 50%;
  background: #22c55e;
  box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.08);
}

.profile-content {
  min-width: 0;
  padding-top: 2px;
}

.profile-tabs :deep(.el-tabs__header) {
  margin-bottom: 20px;
}

.profile-tabs :deep(.el-tabs__item) {
  height: 42px;
  font-size: 14px;
  font-weight: 700;
  color: #64748b;
}

.profile-tabs :deep(.el-tabs__item.is-active) {
  color: #0ea5e9;
}

.profile-tabs :deep(.el-tabs__active-bar) {
  height: 2px;
  background: #0ea5e9;
}

.profile-tabs :deep(.el-tabs__nav-wrap::after) {
  background: #eef2f7;
}

.profile-form :deep(.el-form-item) {
  margin-bottom: 18px;
}

.profile-form :deep(.el-form-item__label) {
  padding-bottom: 7px;
  color: #475569;
  font-size: 13px;
  font-weight: 700;
}

.profile-form :deep(.el-input__wrapper),
.profile-form :deep(.el-select .el-input__wrapper) {
  min-height: 44px;
  border-radius: 10px;
  box-shadow: 0 0 0 1px #dbe3ec inset !important;
  transition: all .2s ease;
}

.profile-form :deep(.el-input__wrapper:hover) {
  box-shadow: 0 0 0 1px #b9c7d7 inset !important;
}

.profile-form :deep(.el-input__wrapper.is-focus) {
  box-shadow: 0 0 0 2px rgba(14, 165, 233, .16) inset, 0 0 0 1px #0ea5e9 inset !important;
}

.profile-form :deep(.el-input.is-disabled .el-input__wrapper) {
  background: #f5f8fb;
}

.form-tip {
  margin-top: 6px;
  color: #94a3b8;
  font-size: 11px;
  line-height: 1.45;
}

.password-rules {
  margin-top: 7px;
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.password-rules span {
  display: flex;
  align-items: center;
  gap: 6px;
  color: #94a3b8;
  font-size: 11px;
}

.password-rules .el-icon {
  font-size: 14px;
}

.rule-valid {
  color: #16a34a !important;
}

.password-match {
  margin-top: 7px;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  color: #ef4444;
  font-size: 11px;
  font-weight: 600;
}

.password-match.valid {
  color: #16a34a;
}

.dialog-footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

.profile-cancel-btn,
.profile-save-btn {
  min-width: 110px;
  border-radius: 10px;
  font-weight: 700;
}

.profile-save-btn {
  background: linear-gradient(135deg, #0ea5e9, #0284c7);
  border: none;
  box-shadow: 0 8px 18px rgba(14, 165, 233, 0.18);
}

@media (max-width: 1100px) {
  .header-bar { padding: 0 18px; }
  .search-box { width: 240px; }
  .ai-btn { display: none; }
}

@media (max-width: 860px) {
  .profile-dialog-body {
    grid-template-columns: 1fr;
  }

  .profile-sidebar {
    padding-right: 0;
    padding-bottom: 18px;
    border-right: none;
    border-bottom: 1px solid #eef2f7;
  }

  .account-summary-card {
    display: grid;
    grid-template-columns: 1fr 1fr;
  }
}

@media (max-width: 560px) {
  .custom-profile-dialog :deep(.el-dialog__body) {
    padding: 18px;
  }

  .custom-profile-dialog :deep(.el-dialog__footer) {
    padding: 0 18px 18px;
  }

  .account-summary-card {
    grid-template-columns: 1fr;
  }

  .dialog-footer {
    flex-direction: column-reverse;
  }

  .profile-cancel-btn,
  .profile-save-btn {
    width: 100%;
  }
}
</style>