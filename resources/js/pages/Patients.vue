<template>
  <div class="patients-page">
    <el-card class="box-card" shadow="never">
      <div class="page-header">
        <h2 class="title">Quản lý Bệnh nhân</h2>
        <span class="subtitle">Quản lý thông tin cá nhân và hồ sơ bệnh án của bệnh nhân</span>
      </div>

      <div class="toolbar">
        <el-button type="primary" class="btn-add" @click="openCreateDialog">
          <el-icon><Plus /></el-icon>
          <span>Thêm Bệnh nhân mới</span>
        </el-button>
        
        <el-input v-model="searchQuery" placeholder="Tìm kiếm theo tên hoặc SĐT..." class="search-input" clearable @input="handleSearch">
          <template #prefix><el-icon><Search /></el-icon></template>
        </el-input>
      </div>

      <el-table v-loading="loading" :data="patients" class="custom-table" style="width: 100%">
        <el-table-column prop="id" label="ID" width="70" align="center" />
        <el-table-column label="Họ và tên" min-width="220">
          <template #default="scope">
              <span class="user-name">{{ scope.row.full_name || scope.row.name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="Giới tính" width="100" align="center">
          <template #default="scope">
            <el-tag :type="scope.row.gender === 'male' || scope.row.gender === 'Nam' ? 'primary' : 'danger'" class="status-tag">
              {{ scope.row.gender }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="Số điện thoại" width="150">
          <template #default="scope">
            <span class="contact-info"><el-icon class="icon-contact"><Phone /></el-icon> {{ scope.row.phone || 'N/A' }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="address" label="Địa chỉ liên hệ" min-width="200" show-overflow-tooltip />
        <el-table-column label="Thao tác" width="140" align="center" fixed="right">
          <template #default="scope">
            <div class="action-buttons">
              <el-tooltip content="Chỉnh sửa" placement="top"><el-button type="primary" link @click="handleEdit(scope.row)"><el-icon :size="18"><Edit /></el-icon></el-button></el-tooltip>
              <el-tooltip content="Xóa" placement="top"><el-button type="danger" link @click="handleDelete(scope.row)"><el-icon :size="18"><Delete /></el-icon></el-button></el-tooltip>
            </div>
          </template>
        </el-table-column>
        <template #empty><el-empty description="Chưa có dữ liệu bệnh nhân" /></template>
      </el-table>
      
      <div class="pagination-wrapper">
        <el-pagination background layout="total, prev, pager, next, jumper" :total="totalPatients" :page-size="10" @current-change="handlePageChange" />
      </div>
    </el-card>

    <!-- DIALOG THÊM / SỬA BỆNH NHÂN -->
    <el-dialog v-model="dialogVisible" :title="isEditMode ? 'Chỉnh sửa thông tin Bệnh nhân' : 'Thêm Bệnh Nhân Mới'" width="550px" destroy-on-close>
      <el-form :model="form" :rules="rules" ref="formRef" label-position="top">
        <el-form-item label="Họ và tên đầy đủ" prop="full_name">
          <el-input v-model="form.full_name" placeholder="Ví dụ: Nguyễn Văn A" size="large" />
        </el-form-item>
        
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="Giới tính" prop="gender">
              <el-select v-model="form.gender" placeholder="Chọn giới tính" size="large" style="width: 100%;">
                <el-option label="Nam" value="male" />
                <el-option label="Nữ" value="female" />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="Ngày sinh" prop="date_of_birth">
              <el-date-picker v-model="form.date_of_birth" type="date" placeholder="Chọn ngày sinh" size="large" style="width: 100%;" value-format="YYYY-MM-DD" />
            </el-form-item>
          </el-col>
        </el-row>

        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="Số điện thoại" prop="phone">
              <el-input v-model="form.phone" placeholder="098xxxxxxx" size="large" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="Email" prop="email">
              <el-input v-model="form.email" placeholder="example@gmail.com" size="large" />
            </el-form-item>
          </el-col>
        </el-row>

        <el-form-item label="Địa chỉ liên hệ" prop="address">
          <el-input v-model="form.address" placeholder="Nhập địa chỉ cư trú..." type="textarea" rows="2" />
        </el-form-item>
      </el-form>

      <template #footer>
        <span class="dialog-footer">
          <el-button @click="dialogVisible = false" size="large">Hủy bỏ</el-button>
          <el-button type="primary" :loading="submitting" @click="submitForm" size="large" class="btn-add">
            {{ isEditMode ? 'Cập nhật' : 'Lưu bệnh nhân' }}
          </el-button>
        </span>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { Plus, Search, Edit, Delete, Phone } from '@element-plus/icons-vue';
import axios from 'axios';
import { ElMessage, ElMessageBox } from 'element-plus';

const patients = ref([]);
const loading = ref(false);
const searchQuery = ref('');
const totalPatients = ref(0);
const currentPage = ref(1);

const dialogVisible = ref(false);
const isEditMode = ref(false);
const currentPatientId = ref(null);
const submitting = ref(false);
const formRef = ref(null);

const form = reactive({
  full_name: '',
  gender: 'male',
  date_of_birth: '',
  phone: '',
  email: '',
  address: ''
});

const rules = {
  full_name: [{ required: true, message: 'Vui lòng nhập họ tên bệnh nhân', trigger: 'blur' }],
  phone: [{ required: true, message: 'Vui lòng nhập số điện thoại', trigger: 'blur' }]
};

const getAvatarLetter = (name) => name ? name.charAt(0).toUpperCase() : 'P';

const fetchPatients = async (page = 1) => {
  loading.value = true;
  currentPage.value = page;
  try {
    const response = await axios.get(`/api/patients?page=${page}&search=${searchQuery.value}`);
    patients.value = response.data.data || response.data;
    totalPatients.value = response.data.meta?.total || response.data.total || patients.value.length;
  } catch (error) {
    console.error(error);
  } finally {
    loading.value = false;
  }
};

const handleSearch = () => fetchPatients(1);
const handlePageChange = (page) => fetchPatients(page);

// Mở form Thêm mới
const openCreateDialog = () => {
  isEditMode.value = false;
  currentPatientId.value = null;
  form.full_name = '';
  form.gender = 'male';
  form.date_of_birth = '';
  form.phone = '';
  form.email = '';
  form.address = '';
  dialogVisible.value = true;
};

// Mở form Chỉnh sửa
const handleEdit = (row) => {
  isEditMode.value = true;
  currentPatientId.value = row.id;
  form.full_name = row.full_name || row.name || '';
  form.gender = row.gender || 'male';
  form.date_of_birth = row.date_of_birth || '';
  form.phone = row.phone || '';
  form.email = row.email || '';
  form.address = row.address || '';
  dialogVisible.value = true;
};

// Xử lý Lưu (Thêm mới hoặc Cập nhật)
const submitForm = async () => {
  if (!formRef.value) return;
  await formRef.value.validate(async (valid) => {
    if (valid) {
      submitting.value = true;
      try {
        if (isEditMode.value) {
          await axios.put(`/api/patients/${currentPatientId.value}`, form);
          ElMessage.success('Cập nhật thông tin bệnh nhân thành công!');
        } else {
          await axios.post('/api/patients', form);
          ElMessage.success('Thêm bệnh nhân mới thành công!');
        }
        dialogVisible.value = false;
        fetchPatients(currentPage.value);
      } catch (error) {
        const errorMsg = error.response?.data?.message || 'Có lỗi xảy ra, vui lòng thử lại!';
        ElMessage.error(errorMsg);
      } finally {
        submitting.value = false;
      }
    }
  });
};

// Xử lý Xóa bệnh nhân
const handleDelete = (row) => {
  ElMessageBox.confirm(
    `Bạn có chắc chắn muốn xóa bệnh nhân "${row.full_name || row.name}" không?`,
    'Cảnh báo xóa',
    {
      confirmButtonText: 'Xóa',
      cancelButtonText: 'Hủy',
      type: 'warning',
    }
  ).then(async () => {
    try {
      await axios.delete(`/api/patients/${row.id}`);
      ElMessage.success('Đã xóa bệnh nhân thành công!');
      fetchPatients(currentPage.value);
    } catch (error) {
      ElMessage.error('Xóa thất bại, vui lòng thử lại!');
    }
  }).catch(() => {});
};

onMounted(() => { fetchPatients(); });
</script>