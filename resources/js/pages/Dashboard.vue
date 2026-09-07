<template>
  <div class="dashboard-page" v-loading="loading">
    <div class="page-header">
      <h2 class="title">Bảng Điều Khiển</h2>
      <p class="subtitle">Tổng quan tình hình hoạt động của phòng khám hôm nay</p>
    </div>

    <el-row :gutter="24" class="stat-cards-row">
      <el-col :xs="24" :sm="12" :lg="6">
        <el-card class="box-card stat-card" shadow="hover">
          <div class="stat-content">
            <div class="stat-info">
              <span class="stat-title">Tổng Bệnh Nhân</span>
              <h3 class="stat-value">{{ stats.total_patients || 0 }}</h3>
              <span class="stat-trend positive">
                <el-icon><Top /></el-icon> Đang quản lý
              </span>
            </div>
            <div class="stat-icon-wrapper bg-blue">
              <el-icon><User /></el-icon>
            </div>
          </div>
        </el-card>
      </el-col>

      <el-col :xs="24" :sm="12" :lg="6">
        <el-card class="box-card stat-card" shadow="hover">
          <div class="stat-content">
            <div class="stat-info">
              <span class="stat-title">Lịch Hẹn Hôm Nay</span>
              <h3 class="stat-value">{{ stats.appointments_today || 0 }}</h3>
              <span class="stat-trend positive">
                <el-icon><Calendar /></el-icon> Đã xác nhận
              </span>
            </div>
            <div class="stat-icon-wrapper bg-green">
              <el-icon><Calendar /></el-icon>
            </div>
          </div>
        </el-card>
      </el-col>

      <el-col :xs="24" :sm="12" :lg="6">
        <el-card class="box-card stat-card" shadow="hover">
          <div class="stat-content">
            <div class="stat-info">
              <span class="stat-title">Tổng Số Bác Sĩ</span>
              <h3 class="stat-value">{{ stats.total_doctors || 0 }}</h3>
              <span class="stat-trend neutral">
                <el-icon><Check /></el-icon> Đang hoạt động
              </span>
            </div>
            <div class="stat-icon-wrapper bg-purple">
              <el-icon><Avatar /></el-icon>
            </div>
          </div>
        </el-card>
      </el-col>

      <el-col :xs="24" :sm="12" :lg="6">
        <el-card class="box-card stat-card" shadow="hover">
          <div class="stat-content">
            <div class="stat-info">
              <span class="stat-title">Doanh Thu (Tháng)</span>
              <h3 class="stat-value">{{ formatCurrency(stats.monthly_revenue || 0) }}</h3>
              <span class="stat-trend positive">
                <el-icon><Money /></el-icon> Thực tế
              </span>
            </div>
            <div class="stat-icon-wrapper bg-orange">
              <el-icon><Money /></el-icon>
            </div>
          </div>
        </el-card>
      </el-col>
    </el-row>

    <el-row :gutter="24" class="main-dashboard-row">
      <el-col :xs="24" :lg="16">
        <el-card class="box-card chart-card" shadow="never">
          <template #header>
            <div class="card-header">
              <span class="card-title">Thống kê Lượt khám & Hoạt động (7 ngày qua)</span>
              <div class="chart-legend">
                <span class="legend-item"><i class="dot blue"></i> Lượt khám</span>
                <span class="legend-item"><i class="dot green"></i> Hoạt động</span>
              </div>
              <el-button type="primary" link @click="fetchDashboardData">Làm mới</el-button>
            </div>
          </template>
          <div class="chart-placeholder">
            <div class="mock-bars" v-if="weeklyStats.length > 0">
              <div 
                class="bar-group" 
                v-for="(item, index) in weeklyStats" 
                :key="index"
              >
                <div class="dual-bars">
                  <div class="bar-wrapper" :title="`${item.date}: ${item.examinations} lượt khám`">
                    <div class="bar exam-bar" :style="{ height: getBarHeight(item.examinations) }">
                      <span class="bar-tooltip">{{ item.examinations }}</span>
                    </div>
                  </div>
                  <div class="bar-wrapper" :title="`${item.date}: ${item.activities} hoạt động`">
                    <div class="bar activity-bar" :style="{ height: getBarHeight(item.activities) }">
                      <span class="bar-tooltip">{{ item.activities }}</span>
                    </div>
                  </div>
                </div>
                <span class="bar-label">{{ formatShortDate(item.date) }}</span>
              </div>
            </div>
            <p class="chart-note" style="margin-top: 15px;">So sánh số lượng Lượt khám và Hoạt động hệ thống theo ngày</p>
          </div>
        </el-card>
      </el-col>

      <el-col :xs="24" :lg="8">
        <el-card class="box-card timeline-card" shadow="never">
          <template #header>
            <div class="card-header">
              <span class="card-title">Hoạt động hệ thống</span>
            </div>
          </template>
          <el-scrollbar height="300px">
            <el-timeline v-if="recentActivities.length > 0">
              <el-timeline-item
                v-for="(activity, index) in recentActivities"
                :key="index"
                type="primary"
                :timestamp="formatDate(activity.created_at)"
              >
                <div class="activity-text">
                  {{ formatActivityText(activity) }}
                </div>
              </el-timeline-item>
            </el-timeline>
            <el-empty v-else description="Chưa có hoạt động nào gần đây" />
          </el-scrollbar>
        </el-card>
      </el-col>
    </el-row>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { User, Calendar, Avatar, Money, Top, Bottom, Check } from '@element-plus/icons-vue';
import { ElMessage } from 'element-plus';

const loading = ref(false);
const stats = ref({
  total_patients: 0,
  appointments_today: 0,
  total_doctors: 0,
  monthly_revenue: 0
});

const recentActivities = ref([]);
const weeklyStats = ref([]);

const formatCurrency = (value) => {
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleString('vi-VN');
};

const formatShortDate = (dateString) => {
  if (!dateString) return '';
  const parts = dateString.split('-');
  return `${parts[2]}/${parts[1]}`;
};

const getBarHeight = (count) => {
  if (!weeklyStats.value.length) return '15%';
  const allCounts = weeklyStats.value.flatMap(i => [i.examinations, i.activities]);
  const max = Math.max(...allCounts, 5);
  const height = (count / max) * 100;
  return Math.max(height, 12) + '%';
};

const formatActivityText = (activity) => {
  const action = (activity.action || '').toUpperCase();
  const subject = (activity.subject_type || '').toLowerCase();
  const id = activity.subject_id;
  const meta = activity.meta || {};

  if (subject === 'payment' || subject === 'invoice') {
    if (meta.is_partial || meta.status === 'partial' || (meta.paid_amount && meta.total_amount && meta.paid_amount < meta.total_amount)) {
      const paid = meta.paid_amount ? formatCurrency(meta.paid_amount) : '';
      return `Đã thanh toán một phần ${paid ? '(' + paid + ')' : ''} cho hóa đơn (Mã: ${id})`;
    }

    if (action === 'PAYMENT_PROCESSED' || action === 'CREATED' || meta.status === 'completed') {
      return `Đã thanh toán thành công hóa đơn (Mã: ${id})`;
    }
    
    if (action === 'STATUS_CHANGED' || action === 'UPDATED') {
      return `Đã cập nhật trạng thái thanh toán của hóa đơn (Mã: ${id})`;
    }
  }

  if (subject === 'appointment') {
    if (action === 'STATUS_CHANGED') {
      return `Đã cập nhật trạng thái lịch hẹn khám (Mã lịch hẹn: ${id})`;
    }
    if (action === 'CREATED') {
      return `Đã tạo lịch hẹn khám bệnh mới (Mã: ${id})`;
    }
  }

  if (subject === 'patient') {
    return `Đã thêm mới hồ sơ bệnh nhân (Mã: ${id})`;
  }

  if (subject === 'doctor') {
    return `Đã cập nhật thông tin bác sĩ (Mã: ${id})`;
  }

  if (subject === 'user') {
    return `Đã đăng ký tài khoản hệ thống mới (Mã: ${id})`;
  }

  const actionsMap = {
    'REGISTERED': 'đã đăng ký',
    'CREATED': 'đã thêm mới',
    'UPDATED': 'đã cập nhật',
    'DELETED': 'đã xóa',
    'STATUS_CHANGED': 'đã thay đổi trạng thái'
  };
  
  const actionText = actionsMap[action] || action;
  return `Đã ${actionText} đối tượng ${subject} (Mã ID: ${id})`;
};

const fetchDashboardData = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/stats');
    if (response.data && response.data.success) {
      stats.value = response.data.data;
      if (response.data.data.recent_activities) {
        recentActivities.value = response.data.data.recent_activities;
      }
      if (response.data.data.weekly_stats) {
        weeklyStats.value = response.data.data.weekly_stats;
      }
    }
  } catch (error) {
    console.error('Lỗi khi tải dữ liệu dashboard:', error);
    ElMessage.error('Không thể tải dữ liệu thống kê từ hệ thống!');
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchDashboardData();
});
</script>

<style scoped>
.dashboard-page {
  animation: fadeIn 0.4s ease-in-out;
}

.stat-cards-row {
  margin-bottom: 24px;
}

.stat-card {
  border-radius: 20px !important;
  transition: all 0.3s ease;
  border: 1px solid rgba(255, 255, 255, 0.9) !important;
}

.stat-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 15px 30px rgba(0, 0, 0, 0.08) !important;
}

.stat-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.stat-info {
  display: flex;
  flex-direction: column;
}

.stat-title {
  font-size: 13px;
  color: #64748b;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.stat-value {
  font-size: 24px;
  font-weight: 800;
  color: #0f172a;
  margin: 8px 0;
}

.stat-trend {
  font-size: 12px;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 4px;
}
.stat-trend.positive { color: #10b981; }
.stat-trend.negative { color: #ef4444; }
.stat-trend.neutral { color: #64748b; }

.stat-icon-wrapper {
  width: 56px;
  height: 56px;
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  color: white;
}
.bg-blue { background: linear-gradient(135deg, #0ea5e9, #0284c7); }
.bg-green { background: linear-gradient(135deg, #34d399, #059669); }
.bg-purple { background: linear-gradient(135deg, #a78bfa, #7c3aed); }
.bg-orange { background: linear-gradient(135deg, #fbbf24, #d97706); }

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.card-title {
  font-size: 16px;
  font-weight: 700;
  color: #0f172a;
}

.chart-legend {
  display: flex;
  gap: 16px;
  font-size: 12px;
  font-weight: 500;
  color: #64748b;
  align-items: center;
}
.legend-item {
  display: flex;
  align-items: center;
  gap: 6px;
}
.dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
}
.dot.blue { background: #0284c7; }
.dot.green { background: #10b981; }

.chart-card, .timeline-card {
  height: 400px;
}

.chart-placeholder {
  height: 280px;
  background: #f1f5f9;
  border-radius: 16px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  border: 1px dashed #cbd5e1;
}

.mock-bars {
  display: flex;
  align-items: flex-end;
  justify-content: space-around;
  height: 160px;
  width: 95%;
  margin-bottom: 8px;
  gap: 8px;
}

.bar-group {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  height: 100%;
  justify-content: flex-end;
}

.dual-bars {
  display: flex;
  width: 100%;
  gap: 4px;
  height: 100%;
  align-items: flex-end;
  justify-content: center;
}

.bar-wrapper {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  height: 100%;
  justify-content: flex-end;
}

.bar {
  width: 100%;
  border-radius: 4px 4px 0 0;
  position: relative;
  transition: all 0.3s ease;
}

.exam-bar {
  background: linear-gradient(to top, #38bdf8, #0284c7);
}
.exam-bar:hover {
  background: linear-gradient(to top, #7dd3fc, #0369a1);
}

.activity-bar {
  background: linear-gradient(to top, #34d399, #059669);
}
.activity-bar:hover {
  background: linear-gradient(to top, #6ee7b7, #047857);
}

.bar-tooltip {
  position: absolute;
  top: -20px;
  left: 50%;
  transform: translateX(-50%);
  font-size: 10px;
  font-weight: bold;
  color: #334155;
}

.bar-label {
  font-size: 11px;
  color: #64748b;
  margin-top: 6px;
}

.chart-note {
  color: #94a3b8;
  font-size: 13px;
  font-weight: 500;
}

.activity-text {
  font-size: 13px;
  color: #334155;
  font-weight: 500;
}s

:deep(.el-timeline-item__content) {
  font-size: 13px;
  color: #334155;
}
:deep(.el-timeline-item__timestamp) {
  font-size: 12px;
  color: #94a3b8;
}
</style>