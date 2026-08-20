<template>
  <div class="examinations-page">
    <el-card class="box-card" shadow="never">
      <div class="page-header">
        <h2 class="title">Quản lý Phiếu khám</h2>
        <span class="subtitle">Theo dõi kết quả chẩn đoán và lập phiếu khám từ lịch hẹn đã xác nhận</span>
      </div>

      <div class="toolbar">
        <el-button type="primary" class="btn-add" @click="openCreateDialog">
          <el-icon><Plus /></el-icon>
          <span>Lập Phiếu khám mới</span>
        </el-button>
        
        <el-input v-model="searchQuery" placeholder="Tìm kiếm phiếu khám..." class="search-input" clearable @input="handleSearch">
          <template #prefix><el-icon><Search /></el-icon></template>
        </el-input>
      </div>

      <el-table v-loading="loading" :data="examinations" class="custom-table" style="width: 100%">
        <el-table-column prop="id" label="ID" width="70" align="center" />
        <el-table-column label="Mã Lịch Hẹn" width="130" align="center">
          <template #default="scope"><strong>#APT-{{ scope.row.appointment_id }}</strong></template>
        </el-table-column>
        <el-table-column label="Bệnh nhân" min-width="180">
          <template #default="scope">
            <span class="user-name" style="color: #0284c7;">
              {{ getPatientName(scope.row) }}
            </span>
          </template>
        </el-table-column>
        <el-table-column label="Bác sĩ khám" min-width="180">
          <template #default="scope">
            <span class="user-name">
              {{ getDoctorName(scope.row) }}
            </span>
          </template>
        </el-table-column>
        <el-table-column prop="diagnosis" label="Chẩn đoán" min-width="220" show-overflow-tooltip />
        <el-table-column prop="examined_at" label="Thời gian khám" width="180" align="center" />
        <el-table-column label="Thao tác" width="140" align="center" fixed="right">
          <template #default="scope">
            <div class="action-buttons">
              <el-tooltip content="Chỉnh sửa phiếu khám" placement="top">
                <el-button type="primary" link @click="handleEdit(scope.row)"><el-icon :size="18"><Edit /></el-icon></el-button>
              </el-tooltip>
              <el-tooltip content="Xóa phiếu khám" placement="top">
                <el-button type="danger" link @click="handleDelete(scope.row)"><el-icon :size="18"><Delete /></el-icon></el-button>
              </el-tooltip>
            </div>
          </template>
        </el-table-column>
        <template #empty><el-empty description="Chưa có dữ liệu phiếu khám nào" /></template>
      </el-table>
      
      <div class="pagination-wrapper">
        <el-pagination background layout="total, prev, pager, next, jumper" :total="totalExaminations" :page-size="15" @current-change="handlePageChange" />
      </div>
    </el-card>

    <!-- DIALOG LẬP / SỬA PHIẾU KHÁM -->
    <el-dialog v-model="dialogVisible" :title="isEditMode ? 'Chỉnh sửa Phiếu khám' : 'Lập Phiếu Khám Mới (Từ Lịch Hẹn)'" width="550px" destroy-on-close>
      <el-form :model="form" :rules="rules" ref="formRef" label-position="top">
        <el-form-item label="Chọn Lịch Hẹn (Trạng thái confirmed)" prop="appointment_id" v-if="!isEditMode">
          <el-select v-model="form.appointment_id" placeholder="Chọn lịch hẹn phù hợp..." size="large" style="width: 100%;" filterable>
            <el-option
              v-for="apt in confirmedAppointments"
              :key="apt.id"
              :label="`#APT-${apt.id} - Bệnh nhân: ${apt.patient?.full_name || apt.patient?.name || 'N/A'} (${apt.scheduled_at})`"
              :value="apt.id"
            />
          </el-select>
        </el-form-item>

        <el-form-item label="Chẩn đoán (Diagnosis)" prop="diagnosis">
          <el-input v-model="form.diagnosis" placeholder="Nhập kết quả chẩn đoán bệnh..." type="textarea" rows="3" size="large" />
        </el-form-item>

        <el-form-item label="Ghi chú (Notes)" prop="notes">
          <el-input v-model="form.notes" placeholder="Nhập ghi chú thêm nếu có..." type="textarea" rows="2" />
        </el-form-item>
      </el-form>

      <template #footer>
        <span class="dialog-footer">
          <el-button @click="dialogVisible = false" size="large">Hủy bỏ</el-button>
          <el-button type="primary" :loading="submitting" @click="submitForm" size="large" class="btn-add">
            {{ isEditMode ? 'Cập nhật' : 'Lập phiếu khám' }}
          </el-button>
        </span>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { Plus, Search, Edit, Delete } from '@element-plus/icons-vue';
import axios from 'axios';
import { ElMessage, ElMessageBox } from 'element-plus';

const examinations = ref([]);
const confirmedAppointments = ref([]);
const patientsList = ref([]);
const doctorsList = ref([]);

const loading = ref(false);
const searchQuery = ref('');
const totalExaminations = ref(0);
const currentPage = ref(1);

const dialogVisible = ref(false);
const isEditMode = ref(false);
const currentExaminationId = ref(null);
const submitting = ref(false);
const formRef = ref(null);

const form = reactive({
  appointment_id: '',
  diagnosis: '',
  notes: ''
});

const rules = {
  appointment_id: [{ required: true, message: 'Vui lòng chọn lịch hẹn', trigger: 'change' }],
  diagnosis: [{ required: true, message: 'Vui lòng nhập kết quả chẩn đoán', trigger: 'blur' }]
};

// Hàm helper để tìm tên bệnh nhân từ danh sách đã load
const getPatientName = (row) => {
  if (row.patient?.full_name) return row.patient.full_name;
  if (row.patient?.name) return row.patient.name;
  const found = patientsList.value.find(p => p.id === row.patient_id);
  return found?.full_name || found?.name || `Bệnh nhân #${row.patient_id}`;
};

// Hàm helper để tìm tên bác sĩ từ danh sách đã load
const getDoctorName = (row) => {
  if (row.doctor?.user?.name) return row.doctor.user.name;
  if (row.doctor?.name) return row.doctor.name;
  const found = doctorsList.value.find(d => d.id === row.doctor_id);
  return found?.user?.name || found?.name || `Bác sĩ #${row.doctor_id}`;
};

const fetchExaminations = async (page = 1) => {
  loading.value = true;
  currentPage.value = page;
  try {
    const response = await axios.get(`/api/examinations?page=${page}`);
    examinations.value = response.data.data || response.data;
    totalExaminations.value = response.data.meta?.total || response.data.total || examinations.value.length;
  } catch (error) {
    console.error(error);
  } finally {
    loading.value = false;
  }
};

const fetchRelations = async () => {
  try {
    const [patientsRes, doctorsRes, aptsRes] = await Promise.all([
      axios.get('/api/patients'),
      axios.get('/api/doctors'),
      axios.get('/api/appointments')
    ]);
    patientsList.value = patientsRes.data.data || patientsRes.data;
    doctorsList.value = doctorsRes.data.data || doctorsRes.data;
    const allApts = aptsRes.data.data || aptsRes.data;
    confirmedAppointments.value = allApts.filter(apt => apt.status === 'confirmed');
  } catch (error) {
    console.error('Không thể tải danh sách quan hệ:', error);
  }
};

const handleSearch = () => fetchExaminations(1);
const handlePageChange = (page) => fetchExaminations(page);

const openCreateDialog = () => {
  isEditMode.value = false;
  currentExaminationId.value = null;
  form.appointment_id = '';
  form.diagnosis = '';
  form.notes = '';
  dialogVisible.value = true;
};

const handleEdit = (row) => {
  isEditMode.value = true;
  currentExaminationId.value = row.id;
  form.appointment_id = row.appointment_id;
  form.diagnosis = row.diagnosis || '';
  form.notes = row.notes || '';
  dialogVisible.value = true;
};

const submitForm = async () => {
  if (!formRef.value) return;
  await formRef.value.validate(async (valid) => {
    if (valid) {
      submitting.value = true;
      try {
        if (isEditMode.value) {
          await axios.put(`/api/examinations/${currentExaminationId.value}`, {
            diagnosis: form.diagnosis,
            notes: form.notes
          });
          ElMessage.success('Cập nhật phiếu khám thành công!');
        } else {
          await axios.post('/api/examinations', form);
          ElMessage.success('Lập phiếu khám thành công và cập nhật lịch hẹn thành hoàn thành!');
        }
        dialogVisible.value = false;
        fetchExaminations(currentPage.value);
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
    `Bạn có chắc chắn muốn xóa phiếu khám #${row.id} không?`,
    'Xác nhận xóa',
    { confirmButtonText: 'Xóa', cancelButtonText: 'Hủy', type: 'warning' }
  ).then(async () => {
    try {
      await axios.delete(`/api/examinations/${row.id}`);
      ElMessage.success('Đã xóa phiếu khám thành công!');
      fetchExaminations(currentPage.value);
    } catch (error) {
      ElMessage.error('Xóa thất bại!');
    }
  }).catch(() => {});
};

onMounted(() => { 
  fetchExaminations(); 
  fetchRelations();
});
</script>