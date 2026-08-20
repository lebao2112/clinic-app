<template>
  <el-container class="layout-container">
    <!-- Sidebar -->
    <el-aside width="260px" class="aside-menu">
      <div class="sidebar-logo">
        <span class="logo-dot"></span>
        <span class="logo-text">ClinicHub</span>
      </div>

      <div class="menu-section-title">Main Menu</div>
      <el-menu
        router
        :default-active="$route.path"
        class="custom-menu"
      >
        <el-menu-item index="/dashboard" v-if="hasAccess(['ADMIN', 'RECEPTIONIST', 'DOCTOR', 'PHARMACIST', 'CASHIER'])">
          <el-icon><Odometer /></el-icon>
          <span>Dashboard</span>
        </el-menu-item>

        <el-menu-item index="/patients" v-if="hasAccess(['ADMIN', 'RECEPTIONIST', 'DOCTOR', 'CASHIER'])">
          <el-icon><User /></el-icon>
          <span>Quản lý Bệnh nhân</span>
        </el-menu-item>
        
        <el-menu-item index="/doctors" v-if="hasAccess(['ADMIN', 'RECEPTIONIST'])">
          <el-icon><Avatar /></el-icon>
          <span>Quản lý Bác sĩ</span>
        </el-menu-item>
        
        <el-menu-item index="/appointments" v-if="hasAccess(['ADMIN', 'RECEPTIONIST', 'DOCTOR', 'CASHIER'])">
          <el-icon><Calendar /></el-icon>
          <span>Lịch hẹn khám</span>
        </el-menu-item>

        <el-menu-item index="/examinations" v-if="hasAccess(['ADMIN', 'DOCTOR', 'RECEPTIONIST'])">
          <el-icon><Files /></el-icon>
          <span>Quản lý Phiếu khám</span>
        </el-menu-item>

        <el-menu-item index="/specialties" v-if="hasAccess(['ADMIN', 'RECEPTIONIST'])">
          <el-icon><FirstAidKit /></el-icon>
          <span>Quản lý Chuyên khoa</span>
        </el-menu-item>

        <el-menu-item index="/medicines" v-if="hasAccess(['ADMIN', 'PHARMACIST', 'DOCTOR'])">
          <el-icon><Box /></el-icon>
          <span>Quản lý Thuốc</span>
        </el-menu-item>

        <el-menu-item index="/prescriptions" v-if="hasAccess(['ADMIN', 'DOCTOR', 'PHARMACIST'])">
          <el-icon><Notebook /></el-icon>
          <span>Quản lý Đơn thuốc</span>
        </el-menu-item>
        
        <el-menu-item index="/invoices" v-if="hasAccess(['ADMIN', 'CASHIER'])">
          <el-icon><Document /></el-icon>
          <span>Quản lý Hóa đơn</span>
        </el-menu-item>

        <el-menu-item index="/users" v-if="hasAccess(['ADMIN'])">
          <el-icon><UserFilled /></el-icon>
          <span>Quản lý Người dùng</span>
        </el-menu-item>
      </el-menu>
    </el-aside>

    <!-- Main Container -->
    <el-container class="main-container">
      <el-header class="header-bar">
        <div class="header-left">
          <div class="search-box">
            <el-icon><Search /></el-icon>
            <input type="text" placeholder="Search..." />
            <span class="shortcut">⌘K</span>
          </div>
        </div>
        
        <div class="header-right">
          <el-button class="ai-btn" type="primary" size="small">
            <el-icon><MagicStick /></el-icon> AI Assistance
          </el-button>
          
          <div class="header-icon-btn" title="Thêm mới"><el-icon><Plus /></el-icon></div>
          <div class="header-icon-btn" title="Lịch trình"><el-icon><Calendar /></el-icon></div>
          <div class="header-icon-btn" title="Thông báo"><el-icon><Bell /></el-icon></div>

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
                <el-dropdown-item>Thông tin cá nhân</el-dropdown-item>
                <el-dropdown-item divided @click="handleLogout" style="color: #ef4444;">Đăng xuất</el-dropdown-item>
              </el-dropdown-menu>
            </template>
          </el-dropdown>
        </div>
      </el-header>

      <el-main class="main-content">
        <div class="content-wrapper">
          <router-view></router-view>
        </div>
      </el-main>
    </el-container>
  </el-container>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { 
  Odometer, User, Avatar, Calendar, Files, Document, ArrowDown, 
  Search, MagicStick, Plus, Bell, FirstAidKit, UserFilled,
  Box, Notebook 
} from '@element-plus/icons-vue';
import { ElMessage } from 'element-plus';

const router = useRouter();
const currentUser = ref({ name: '', role: 'ADMIN' }); 

const fetchCurrentUser = async () => {
  try {
    const response = await axios.get('/api/me');
    currentUser.value = response.data.data || response.data;
  } catch (error) {
    console.error('Lỗi khi lấy thông tin người dùng:', error);
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
    console.error('Lỗi API logout:', error);
  } finally {
    localStorage.removeItem('auth_token');
    ElMessage.success('Đã đăng xuất thành công!');
    router.push('/login');
  }
};

onMounted(() => {
  fetchCurrentUser();
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
}
.sidebar-logo {
  height: 72px;
  display: flex;
  align-items: center;
  padding: 0 24px;
  gap: 10px;
  border-bottom: 1px solid #f1f5f9;
}
.logo-dot {
  width: 12px;
  height: 12px;
  background: #0ea5e9;
  border-radius: 4px;
}
.logo-text {
  font-size: 20px;
  font-weight: 800;
  color: #0f172a;
  letter-spacing: -0.5px;
}
.menu-section-title {
  padding: 24px 24px 8px 24px;
  font-size: 11px;
  font-weight: 700;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.8px;
}
.custom-menu {
  border-right: none !important;
  background-color: transparent !important;
  padding: 0 16px;
}
.custom-menu :deep(.el-menu-item) {
  height: 48px !important;
  line-height: 48px !important;
  border-radius: 12px !important;
  margin-bottom: 6px !important;
  color: #64748b !important;
  font-weight: 600;
  transition: all 0.2s ease !important;
}
.custom-menu :deep(.el-menu-item:hover) {
  background-color: #f8fafc !important;
  color: #0ea5e9 !important;
}
.custom-menu :deep(.el-menu-item.is-active) {
  background-color: #eff6ff !important;
  color: #0284c7 !important;
  font-weight: 700;
}
.custom-menu :deep(.el-menu-item .el-icon) {
  margin-right: 12px;
  font-size: 18px;
  color: #64748b;
}
.custom-menu :deep(.el-menu-item.is-active .el-icon) {
  color: #0284c7 !important;
}
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
</style>