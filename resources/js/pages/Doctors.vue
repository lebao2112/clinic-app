<template>
  <div class="doctors-page">
    <el-card class="box-card" shadow="never">
      <div class="page-header">
        <h2 class="title">Quản lý Bác sĩ</h2>
        <span class="subtitle">Quản lý thông tin hồ sơ và chứng chỉ hành nghề của đội ngũ y bác sĩ</span>
      </div>

      <div class="toolbar">
        <el-button type="primary" class="btn-add" @click="openCreateDialog">
          <el-icon><Plus /></el-icon>
          <span>Thêm Bác sĩ mới</span>
        </el-button>
        
        <el-input v-model="searchQuery" placeholder="Tìm kiếm bác sĩ theo tên hoặc email..." class="search-input" clearable @input="handleSearch">
          <template #prefix><el-icon><Search /></el-icon></template>
        </el-input>
      </div>

      <el-table v-loading="loading" :data="doctors" class="custom-table" style="width: 100%">
        <el-table-column prop="id" label="ID" width="70" align="center" />
        <el-table-column label="Họ và tên Bác sĩ" min-width="220">
          <template #default="scope">
            <span class="user-name">{{ scope.row.user?.name || scope.row.name || `Bác sĩ #${scope.row.id}` }}</span>
          </template>
        </el-table-column>
        <el-table-column label="Email liên hệ" min-width="200">
          <template #default="scope">
            <span class="contact-info"><el-icon class="icon-email"><Message /></el-icon> {{ scope.row.user?.email || 'N/A' }}</span>
          </template>
        </el-table-column>
        <el-table-column label="Số chứng chỉ" width="200" align="center">
          <template #default="scope"><el-tag type="info" class="license-tag">{{ scope.row.license_number }}</el-tag></template>
        </el-table-column>
        <el-table-column prop="bio" label="Chuyên môn / Tiểu sử" min-width="250" show-overflow-tooltip />
        <el-table-column label="Thao tác" width="140" align="center" fixed="right">
          <template #default="scope">
            <div class="action-buttons">
              <el-tooltip content="Chỉnh sửa" placement="top"><el-button type="primary" link @click="handleEdit(scope.row)"><el-icon :size="18"><Edit /></el-icon></el-button></el-tooltip>
              <el-tooltip content="Xóa" placement="top"><el-button type="danger" link @click="handleDelete(scope.row)"><el-icon :size="18"><Delete /></el-icon></el-button></el-tooltip>
            </div>
          </template>
        </el-table-column>
        <template #empty><el-empty description="Chưa có dữ liệu bác sĩ nào" /></template>
      </el-table>
      
      <div class="pagination-wrapper">
        <el-pagination background layout="total, prev, pager, next, jumper" :total="totalDoctors" :page-size="10" @current-change="handlePageChange" />
      </div>
    </el-card>

    <!-- DIALOG THÊM / SỬA BÁC SĨ -->
    <el-dialog v-model="dialogVisible" :title="isEditMode ? 'Chỉnh sửa thông tin Bác sĩ' : 'Thêm Bác Sĩ Mới'" width="500px" destroy-on-close>
      <el-form :model="form" :rules="rules" ref="formRef" label-position="top">
        <!-- Thay thế input thường bằng el-select để chọn tài khoản user -->
        <el-form-item label="Chọn tài khoản Người dùng (User)" prop="user_id">
          <el-select v-model="form.user_id" placeholder="Chọn user tương ứng..." size="large" style="width: 100%;" filterable>
            <el-option
              v-for="u in usersList"
              :key="u.id"
              :label="`${u.name} (${u.email})`"
              :value="u.id"
            />
          </el-select>
        </el-form-item>

        <!-- Thay thế input thường bằng el-select để chọn chuyên khoa -->
        <el-form-item label="Chọn Chuyên khoa (Specialty)" prop="specialty_id">
          <el-select v-model="form.specialty_id" placeholder="Chọn chuyên khoa..." size="large" style="width: 100%;" filterable>
            <el-option
              v-for="s in specialtiesList"
              :key="s.id"
              :label="s.name || s.title"
              :value="s.id"
            />
          </el-select>
        </el-form-item>

        <el-form-item label="Số chứng chỉ hành nghề" prop="license_number">
          <el-input v-model="form.license_number" placeholder="Ví dụ: MED-123456789" size="large" />
        </el-form-item>

        <el-form-item label="Tiểu sử / Chuyên môn" prop="bio">
          <el-input v-model="form.bio" placeholder="Nhập mô tả tiểu sử..." type="textarea" rows="3" />
        </el-form-item>
      </el-form>

      <template #footer>
        <span class="dialog-footer">
          <el-button @click="dialogVisible = false" size="large">Hủy bỏ</el-button>
          <el-button type="primary" :loading="submitting" @click="submitForm" size="large" class="btn-add">
            {{ isEditMode ? 'Cập nhật' : 'Lưu bác sĩ' }}
          </el-button>
        </span>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { Plus, Search, Edit, Delete, Message } from '@element-plus/icons-vue';
import axios from 'axios';
import { ElMessage, ElMessageBox } from 'element-plus';

const doctors = ref([]);
const usersList = ref([]);
const specialtiesList = ref([]);

const loading = ref(false);
const searchQuery = ref('');
const totalDoctors = ref(0);
const currentPage = ref(1);

const dialogVisible = ref(false);
const isEditMode = ref(false);
const currentDoctorId = ref(null);
const submitting = ref(false);
const formRef = ref(null);

const form = reactive({
  user_id: '',
  specialty_id: '',
  license_number: '',
  bio: ''
});

const rules = {
  user_id: [{ required: true, message: 'Vui lòng chọn Người dùng', trigger: 'change' }],
  specialty_id: [{ required: true, message: 'Vui lòng chọn Chuyên khoa', trigger: 'change' }],
  license_number: [{ required: true, message: 'Vui lòng nhập số chứng chỉ', trigger: 'blur' }]
};

// Lấy danh sách bác sĩ
const fetchDoctors = async (page = 1) => {
  loading.value = true;
  currentPage.value = page;
  try {
    const response = await axios.get(`/api/doctors?page=${page}&search=${searchQuery.value}`);
    doctors.value = response.data.data || response.data;
    totalDoctors.value = response.data.meta?.total || response.data.total || doctors.value.length;
  } catch (error) {
    console.error(error);
  } finally {
    loading.value = false;
  }
};

// Lấy danh sách Users và Specialties để đổ vào thẻ Select
const fetchRelationData = async () => {
  try {
    const [usersRes, specialtiesRes] = await Promise.all([
      axios.get('/api/users'),
      axios.get('/api/specialties')
    ]);
    usersList.value = usersRes.data.data || usersRes.data;
    specialtiesList.value = specialtiesRes.data.data || specialtiesRes.data;
  } catch (error) {
    console.error('Không thể tải dữ liệu liên quan (Users/Specialties):', error);
  }
};

const handleSearch = () => fetchDoctors(1);
const handlePageChange = (page) => fetchDoctors(page);

const openCreateDialog = () => {
  isEditMode.value = false;
  currentDoctorId.value = null;
  form.user_id = '';
  form.specialty_id = '';
  form.license_number = '';
  form.bio = '';
  dialogVisible.value = true;
};

const handleEdit = (row) => {
  isEditMode.value = true;
  currentDoctorId.value = row.id;
  form.user_id = row.user_id || '';
  form.specialty_id = row.specialty_id || '';
  form.license_number = row.license_number || '';
  form.bio = row.bio || '';
  dialogVisible.value = true;
};

const submitForm = async () => {
  if (!formRef.value) return;
  await formRef.value.validate(async (valid) => {
    if (valid) {
      submitting.value = true;
      try {
        if (isEditMode.value) {
          await axios.put(`/api/doctors/${currentDoctorId.value}`, form);
          ElMessage.success('Cập nhật thông tin bác sĩ thành công!');
        } else {
          await axios.post('/api/doctors', form);
          ElMessage.success('Thêm bác sĩ mới thành công!');
        }
        dialogVisible.value = false;
        fetchDoctors(currentPage.value);
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
    `Bạn có chắc chắn muốn xóa bác sĩ #${row.id} không?`,
    'Cảnh báo xóa',
    {
      confirmButtonText: 'Xóa',
      cancelButtonText: 'Hủy',
      type: 'warning',
    }
  ).then(async () => {
    try {
      await axios.delete(`/api/doctors/${row.id}`);
      ElMessage.success('Đã xóa bác sĩ thành công!');
      fetchDoctors(currentPage.value);
    } catch (error) {
      ElMessage.error('Xóa thất bại, vui lòng thử lại!');
    }
  }).catch(() => {});
};

onMounted(() => { 
  fetchDoctors(); 
  fetchRelationData(); // Gọi đồng thời dữ liệu select khi tải trang
});
</script>