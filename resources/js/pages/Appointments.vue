<template>
  <div class="appointments-page">
    <el-card class="box-card" shadow="never">
      <div class="page-header">
        <h2 class="title">Lịch Hẹn Khám</h2>
        <span class="subtitle">Theo dõi và quản lý lịch đặt khám của bệnh nhân với bác sĩ</span>
      </div>

      <div class="toolbar">
        <el-button type="primary" class="btn-add" @click="openCreateDialog">
          <el-icon><Plus /></el-icon>
          <span>Thêm Lịch hẹn mới</span>
        </el-button>
        
        <el-input v-model="searchQuery" placeholder="Tìm kiếm theo mã, tên bệnh nhân..." class="search-input" clearable @input="handleSearch">
          <template #prefix><el-icon><Search /></el-icon></template>
        </el-input>
      </div>

      <el-table v-loading="loading" :data="appointments" class="custom-table" style="width: 100%">
        <el-table-column prop="id" label="Mã Lịch Hẹn" width="120" align="center">
          <template #default="scope"><strong>#APT-{{ scope.row.id }}</strong></template>
        </el-table-column>
        <el-table-column label="Bệnh nhân" min-width="180">
          <template #default="scope"><span class="user-name" style="color: #0284c7;">{{ scope.row.patient?.full_name || scope.row.patient?.name || 'N/A' }}</span></template>
        </el-table-column>
        <el-table-column label="Bác sĩ phụ trách" min-width="180">
          <template #default="scope"><span class="user-name">{{ scope.row.doctor?.user?.name || scope.row.doctor?.name || 'Chưa phân công' }}</span></template>
        </el-table-column>
        <el-table-column label="Thời gian hẹn" width="180" align="center">
          <template #default="scope"><span class="contact-info"><el-icon class="icon-contact"><Clock /></el-icon> {{ scope.row.scheduled_at || 'N/A' }}</span></template>
        </el-table-column>
        
        <!-- TRẠNG THÁI: Tích hợp Dropdown bấm trực tiếp ngay ngoài bảng -->
        <el-table-column label="Trạng thái" width="160" align="center">
          <template #default="scope">
            <el-dropdown trigger="click" @command="(cmd) => handleStatusChange(scope.row.id, cmd)">
              <span class="el-dropdown-link" style="cursor: pointer;">
                <el-tag :type="getStatusType(scope.row.status)" class="status-tag">
                  {{ scope.row.status }} <el-icon class="el-icon--right"><ArrowDown /></el-icon>
                </el-tag>
              </span>
              <template #dropdown>
                <el-dropdown-menu>
                  <el-dropdown-item command="confirmed" :disabled="!canTransition(scope.row.status, 'confirmed')">
                    👉 Đổi thành: confirmed
                  </el-dropdown-item>
                  <el-dropdown-item command="completed" :disabled="!canTransition(scope.row.status, 'completed')">
                    ✅ Đổi thành: completed
                  </el-dropdown-item>
                  <el-dropdown-item command="cancelled" :disabled="!canTransition(scope.row.status, 'cancelled')" style="color: #ef4444;">
                    ❌ Đổi thành: cancelled
                  </el-dropdown-item>
                </el-dropdown-menu>
              </template>
            </el-dropdown>
          </template>
        </el-table-column>

        <el-table-column label="Thao tác" width="140" align="center" fixed="right">
          <template #default="scope">
            <div class="action-buttons">
              <el-tooltip content="Chỉnh sửa thông tin" placement="top"><el-button type="primary" link @click="handleEdit(scope.row)"><el-icon :size="18"><Edit /></el-icon></el-button></el-tooltip>
              <el-tooltip content="Xóa" placement="top"><el-button type="danger" link @click="handleDelete(scope.row)"><el-icon :size="18"><Delete /></el-icon></el-button></el-tooltip>
            </div>
          </template>
        </el-table-column>
        <template #empty><el-empty description="Không có lịch hẹn nào" /></template>
      </el-table>
      
      <div class="pagination-wrapper">
        <el-pagination background layout="total, prev, pager, next, jumper" :total="totalAppointments" :page-size="10" @current-change="handlePageChange" />
      </div>
    </el-card>

    <!-- DIALOG THÊM / SỬA LỊCH HẸN -->
    <el-dialog v-model="dialogVisible" :title="isEditMode ? 'Chỉnh sửa Lịch hẹn khám' : 'Thêm Lịch hẹn mới'" width="500px" destroy-on-close>
      <el-form :model="form" :rules="rules" ref="formRef" label-position="top">
        <el-form-item label="Chọn Bệnh nhân" prop="patient_id">
          <el-select v-model="form.patient_id" placeholder="Chọn bệnh nhân..." size="large" style="width: 100%;" filterable>
            <el-option
              v-for="p in patientsList"
              :key="p.id"
              :label="`${p.full_name || p.name} (${p.phone || 'Không có SĐT'})`"
              :value="p.id"
            />
          </el-select>
        </el-form-item>

        <el-form-item label="Chọn Bác sĩ phụ trách" prop="doctor_id">
          <el-select v-model="form.doctor_id" placeholder="Chọn bác sĩ..." size="large" style="width: 100%;" filterable>
            <el-option
              v-for="d in doctorsList"
              :key="d.id"
              :label="d.user?.name || d.name || `Bác sĩ #${d.id}`"
              :value="d.id"
            />
          </el-select>
        </el-form-item>

        <el-form-item label="Thời gian khám (Scheduled At)" prop="scheduled_at">
          <el-date-picker
            v-model="form.scheduled_at"
            type="datetime"
            placeholder="Chọn ngày và giờ khám..."
            size="large"
            style="width: 100%;"
            value-format="YYYY-MM-DD HH:mm:ss"
          />
        </el-form-item>

        <el-form-item label="Lý do khám / Triệu chứng" prop="reason">
          <el-input v-model="form.reason" placeholder="Nhập lý do khám..." type="textarea" rows="2" />
        </el-form-item>
      </el-form>

      <template #footer>
        <span class="dialog-footer">
          <el-button @click="dialogVisible = false" size="large">Hủy bỏ</el-button>
          <el-button type="primary" :loading="submitting" @click="submitForm" size="large" class="btn-add">
            {{ isEditMode ? 'Cập nhật' : 'Lưu lịch hẹn' }}
          </el-button>
        </span>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { Plus, Search, Edit, Delete, Clock, ArrowDown } from '@element-plus/icons-vue';
import axios from 'axios';
import { ElMessage, ElMessageBox } from 'element-plus';

const appointments = ref([]);
const patientsList = ref([]);
const doctorsList = ref([]);

const loading = ref(false);
const searchQuery = ref('');
const totalAppointments = ref(0);
const currentPage = ref(1);

const dialogVisible = ref(false);
const isEditMode = ref(false);
const currentAppointmentId = ref(null);
const submitting = ref(false);
const formRef = ref(null);

const form = reactive({
  patient_id: '',
  doctor_id: '',
  scheduled_at: '',
  reason: ''
});

const rules = {
  patient_id: [{ required: true, message: 'Vui lòng chọn bệnh nhân', trigger: 'change' }],
  doctor_id: [{ required: true, message: 'Vui lòng chọn bác sĩ', trigger: 'change' }],
  scheduled_at: [{ required: true, message: 'Vui lòng chọn thời gian khám', trigger: 'change' }]
};

const getStatusType = (status) => {
  const map = { 'scheduled': 'warning', 'confirmed': 'primary', 'completed': 'success', 'cancelled': 'danger' };
  return map[status] || 'info';
};

// Kiểm tra trạng thái có được phép chuyển đổi theo logic backend hay không[cite: 13]
const canTransition = (current, target) => {
  const validTransitions = {
    'scheduled': ['confirmed', 'cancelled'],
    'confirmed': ['completed', 'cancelled'],
    'cancelled': [],
    'completed': []
  };
  return validTransitions[current]?.includes(target) || false;
};

// Gọi API chuyên biệt đổi trạng thái (PATCH /api/appointments/{id}/status)[cite: 1, 13]
const handleStatusChange = async (id, newStatus) => {
  try {
    await axios.patch(`/api/appointments/${id}/status`, { status: newStatus });
    ElMessage.success('Đổi trạng thái lịch hẹn thành công!');
    fetchAppointments(currentPage.value);
  } catch (error) {
    const errorMsg = error.response?.data?.message || 'Không thể chuyển đổi trạng thái này theo quy định!';
    ElMessage.error(errorMsg);
  }
};

const fetchAppointments = async (page = 1) => {
  loading.value = true;
  currentPage.value = page;
  try {
    const response = await axios.get(`/api/appointments?page=${page}&search=${searchQuery.value}`);
    appointments.value = response.data.data || response.data;
    totalAppointments.value = response.data.meta?.total || response.data.total || appointments.value.length;
  } catch (error) {
    console.error(error);
  } finally {
    loading.value = false;
  }
};

const fetchRelations = async () => {
  try {
    const [patientsRes, doctorsRes] = await Promise.all([
      axios.get('/api/patients'),
      axios.get('/api/doctors')
    ]);
    patientsList.value = patientsRes.data.data || patientsRes.data;
    doctorsList.value = doctorsRes.data.data || doctorsRes.data;
  } catch (error) {
    console.error('Lỗi khi tải danh sách bệnh nhân/bác sĩ:', error);
  }
};

const handleSearch = () => fetchAppointments(1);
const handlePageChange = (page) => fetchAppointments(page);

const openCreateDialog = () => {
  isEditMode.value = false;
  currentAppointmentId.value = null;
  form.patient_id = '';
  form.doctor_id = '';
  form.scheduled_at = '';
  form.reason = '';
  dialogVisible.value = true;
};

const handleEdit = (row) => {
  isEditMode.value = true;
  currentAppointmentId.value = row.id;
  form.patient_id = row.patient_id || '';
  form.doctor_id = row.doctor_id || '';
  form.scheduled_at = row.scheduled_at || '';
  form.reason = row.reason || '';
  dialogVisible.value = true;
};

const submitForm = async () => {
  if (!formRef.value) return;
  await formRef.value.validate(async (valid) => {
    if (valid) {
      submitting.value = true;
      try {
        if (isEditMode.value) {
          await axios.put(`/api/appointments/${currentAppointmentId.value}`, form);
          ElMessage.success('Cập nhật lịch hẹn thành công!');
        } else {
          await axios.post('/api/appointments', form);
          ElMessage.success('Thêm lịch hẹn mới thành công!');
        }
        dialogVisible.value = false;
        fetchAppointments(currentPage.value);
      } catch (error) {
        const errorMsg = error.response?.data?.message || 'Có lỗi xảy ra, vui lòng thử lại!';
        ElMessage.error(errorMsg);
      } finally {
        submitting.value = false;
      }
    }
  });
};

const handleDelete = (row) => {
  ElMessageBox.confirm(
    `Bạn có chắc chắn muốn xóa lịch hẹn #${row.id} không?`,
    'Cảnh báo xóa',
    { confirmButtonText: 'Xóa', cancelButtonText: 'Hủy', type: 'warning' }
  ).then(async () => {
    try {
      await axios.delete(`/api/appointments/${row.id}`);
      ElMessage.success('Đã xóa lịch hẹn thành công!');
      fetchAppointments(currentPage.value);
    } catch (error) {
      ElMessage.error('Xóa thất bại!');
    }
  }).catch(() => {});
};

onMounted(() => {
  fetchAppointments();
  fetchRelations();
});
</script>