<template>
  <div class="login-page">
    <div class="login-container">
      
      <!-- NỬA BÊN TRÁI -->
      <div class="login-left-side">
        <div class="overlay-gradient"></div>

        <div class="left-content-top">
          <div class="brand-badge">
            <el-icon class="shield-icon"><FirstAidKit /></el-icon>
          </div>
          <h2>Clinic Management</h2>
          <p class="brand-slogan">Hệ thống quản lý phòng khám chuyên nghiệp,<br>minh bạch và tận tâm.</p>
        </div>

        <!-- Khung chứa ảnh -->
        <div class="illustration-box">
          <img :src="'/images/image.jpeg'" alt="Medical Illustration" />
        </div>
      </div>

      <!-- NỬA BÊN PHẢI: Form Đăng nhập -->
      <div class="login-right-side">
        <div class="form-wrapper">
          <h2 class="form-title">ĐĂNG NHẬP HỆ THỐNG</h2>
          
          <el-form 
            :model="form" 
            :rules="rules" 
            ref="formRef" 
            class="custom-form"
            @keyup.enter="handleLogin"
            label-position="top"
          >
            <el-form-item label="Tài khoản (Email)" prop="email">
              <el-input 
                v-model="form.email" 
                placeholder="admin@clinic.test" 
                size="large"
                clearable
              />
            </el-form-item>
            
            <el-form-item label="Mật khẩu" prop="password">
              <el-input 
                v-model="form.password" 
                type="password" 
                placeholder="••••••••" 
                size="large" 
                show-password
              />
            </el-form-item>

            <div class="form-actions-row">
              <el-checkbox v-model="form.remember">Ghi nhớ đăng nhập</el-checkbox>
              <a href="#" class="forgot-link" @click.prevent>Quên mật khẩu?</a>
            </div>
            
            <el-form-item style="margin-top: 24px;">
              <el-button 
                type="primary" 
                class="btn-login-submit" 
                :loading="loading" 
                @click="handleLogin"
              >
                Đăng nhập
              </el-button>
            </el-form-item>
          </el-form>
          
          <div class="form-bottom-text">
            <span>© 2026 Clinic Management System</span>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { FirstAidKit } from '@element-plus/icons-vue';
import { ElMessage } from 'element-plus';

const router = useRouter();
const formRef = ref(null);
const loading = ref(false);

const form = reactive({
  email: '',
  password: '',
  remember: false
});

const rules = {
  email: [
    { required: true, message: 'Vui lòng nhập tài khoản email', trigger: 'blur' }
  ],
  password: [
    { required: true, message: 'Vui lòng nhập mật khẩu', trigger: 'blur' },
    { min: 6, message: 'Mật khẩu phải từ 6 ký tự trở lên', trigger: 'blur' }
  ]
};

const handleLogin = async () => {
  if (!formRef.value) return;
  
  await formRef.value.validate(async (valid) => {
    if (valid) {
      loading.value = true;
      try {
        await axios.get('/sanctum/csrf-cookie');
        const response = await axios.post('/api/login', {
          email: form.email,
          password: form.password
        });
        
        const token = response.data.token || response.data.access_token || response.data.data?.token;
        
        if (token) {
          localStorage.setItem('auth_token', token);
          axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
        }
        
        ElMessage.success('Đăng nhập thành công!');
        router.push('/patients');
        
      } catch (error) {
        const errorMsg = error.response?.data?.message || 'Tài khoản hoặc mật khẩu không chính xác!';
        ElMessage.error(errorMsg);
      } finally {
        loading.value = false;
      }
    }
  });
};
</script>

<style scoped>
/* BACKGROUND TOÀN TRANG */
.login-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, rgba(15, 23, 42, 0.75), rgba(2, 132, 199, 0.7)), 
              url('/images/background.jpg') center/cover no-repeat;
  font-family: 'Plus Jakarta Sans', sans-serif;
  padding: 20px;
}

.login-container {
  width: 100%;
  max-width: 1024px;
  min-height: 600px;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-radius: 24px;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.3);
  display: flex;
  overflow: hidden;
  border: 1px solid rgba(255, 255, 255, 0.8);
}

.login-left-side {
  flex: 1.4;
  background: linear-gradient(135deg, #38bdf8 0%, #0284c7 50%, #2563eb 100%);
  padding: 48px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: space-between;
  text-align: center;
  color: white;
  position: relative;
  overflow: hidden;
}

.overlay-gradient {
  position: absolute;
  top: 0; left: 0; right: 0; bottom: 0;
  background: linear-gradient(135deg, rgba(2, 132, 199, 0.85) 0%, rgba(37, 99, 235, 0.9) 100%);
  z-index: 1;
}

.left-content-top {
  position: relative;
  z-index: 2;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.brand-badge {
  width: 60px;
  height: 60px;
  background: rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(10px);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid rgba(255, 255, 255, 0.4);
  margin-bottom: 16px;
}
.shield-icon { font-size: 30px; color: white; }

.login-left-side h2 {
  font-size: 26px;
  font-weight: 800;
  margin: 0 0 8px 0;
  letter-spacing: -0.5px;
}

.brand-slogan {
  font-size: 15px; 
  color: #ffffff;
  line-height: 1.6;
  margin: 0;
  font-weight: 600;
}

/* =======================================
   ĐÃ SỬA: Ảnh to hơn, bo góc mềm mại, bỏ hover 
======================================= */
.illustration-box {
  position: relative;
  z-index: 2;
  width: 100%;
  max-width: 420px; /* Nới rộng kích thước ảnh */
  height: 240px;    /* Tăng chiều cao để ảnh cân đối */
  margin-bottom: 24px;
  border-radius: 20px; /* Bo góc mềm mại */
  overflow: hidden;    /* Cắt gọn ảnh vừa vặn vào khung bo góc */
  box-shadow: 0 15px 35px rgba(0, 0, 0, 0.25); /* Đổ bóng 3D */
}

.illustration-box img {
  width: 100%;
  height: 100%;
  object-fit: cover; /* Ép ảnh lấp đầy khung mà không bị méo */
  display: block;
}

.login-right-side {
  flex: 1;
  background: #ffffff;
  padding: 48px 56px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.form-wrapper {
  width: 100%;
  max-width: 340px;
}

.form-title {
  text-align: center;
  font-size: 20px;
  font-weight: 800;
  color: #0f172a;
  margin: 0 0 32px 0;
  letter-spacing: 0.5px;
}

:deep(.el-form-item__label) {
  font-weight: 700 !important;
  color: #334155 !important;
  font-size: 13px;
  margin-bottom: 6px !important;
}

:deep(.el-input__wrapper) {
  background-color: #f8fafc !important;
  border: 1px solid #e2e8f0 !important;
  border-radius: 12px !important;
  box-shadow: none !important;
  padding: 6px 14px;
  transition: all 0.3s ease;
}

:deep(.el-input__wrapper.is-focus) {
  border-color: #0284c7 !important;
  background-color: #ffffff !important;
  box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.1) !important;
}

:deep(.el-input__inner) {
  color: #0f172a !important;
  font-size: 14px;
  font-weight: 500;
}

.form-actions-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 8px;
}

.forgot-link {
  font-size: 13px;
  color: #0284c7;
  text-decoration: none;
  font-weight: 600;
}
.forgot-link:hover { text-decoration: underline; }

.btn-login-submit {
  width: 100%;
  height: 48px;
  background: linear-gradient(135deg, #38bdf8 0%, #0284c7 100%) !important;
  border: none !important;
  border-radius: 12px !important;
  font-size: 15px;
  font-weight: 700;
  color: white;
  box-shadow: 0 8px 20px rgba(2, 132, 199, 0.3);
  transition: all 0.3s ease;
}
.btn-login-submit:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 25px rgba(2, 132, 199, 0.4);
}

.form-bottom-text {
  text-align: center;
  margin-top: 24px;
  font-size: 13px;
  color: #64748b;
  font-weight: 500;
}
</style>