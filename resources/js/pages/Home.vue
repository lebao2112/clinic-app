<template>
  <div class="landing-page">
    <!-- ================= HEADER ================= -->
    <header class="navbar">
      <div class="container nav-container">
        <div class="logo">
          <el-icon class="logo-icon"><FirstAidKit /></el-icon>
          <div class="logo-text">
            <span class="brand-name">CLINIC</span>
            <span class="brand-sub">MANAGEMENT</span>
          </div>
        </div>

        <nav class="nav-links">
          <a href="#" class="active">Trang chủ</a>
          <a href="#services">Dịch vụ</a>
          <a href="#doctors">Bác sĩ</a>
          <a href="#">Tin tức</a>
          <a href="#">Liên hệ</a>
        </nav>

        <div class="nav-actions">
          <div class="hotline">
            <el-icon><Phone /></el-icon>
            <span>1900 1234</span>
          </div>
          <!-- Nút Đăng nhập gọi chính xác route /login -->
          <el-button plain class="btn-login" @click="goToLogin">Đăng nhập</el-button>
          <el-button type="primary" class="btn-book" @click="openBookingDialog()">Đặt lịch khám</el-button>
        </div>
      </div>
    </header>

    <!-- ================= HERO SECTION ================= -->
    <section class="hero-section">
      <div class="container hero-container">
        <div class="hero-content">
          <h1 class="hero-title">
            Chăm sóc sức khỏe <br>
            tận tâm – <span class="highlight">Toàn diện</span>
          </h1>
          <p class="hero-desc">
            Hệ thống phòng khám đa khoa tiêu chuẩn quốc tế với đội ngũ y bác sĩ đầu ngành, hệ thống trang thiết bị hiện đại, cam kết mang lại dịch vụ y tế chất lượng cao nhất cho gia đình bạn.
          </p>
          <div class="hero-buttons">
            <el-button type="primary" size="large" class="btn-primary" @click="openBookingDialog()">
              <el-icon><Calendar /></el-icon> Đặt lịch khám ngay
            </el-button>
            <el-button plain size="large" class="btn-secondary" @click="scrollToDoctors">
              <el-icon><User /></el-icon> Xem bác sĩ
            </el-button>
          </div>
          
          <div class="hero-badges">
            <div class="badge-item"><el-icon><Medal /></el-icon> Bác sĩ chuyên môn cao</div>
            <div class="badge-item"><el-icon><Monitor /></el-icon> Thiết bị hiện đại</div>
            <div class="badge-item"><el-icon><Service /></el-icon> Dịch vụ tận tâm</div>
            <div class="badge-item"><el-icon><Wallet /></el-icon> Chi phí hợp lý</div>
          </div>
        </div>

        <div class="hero-image">
          <img src="https://images.unsplash.com/photo-1584515933487-779824d29309?w=1200" alt="Doctor and Patient" />
          <div class="floating-card">
            <el-icon class="float-icon"><FirstAidKit /></el-icon>
            <div>
              <strong>CLINIC</strong>
              <p>MANAGEMENT SYSTEM</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ================= DỊCH VỤ NỔI BẬT ================= -->
    <section id="services" class="services-section">
      <div class="container">
        <div class="section-header">
          <h2 class="section-title">DỊCH VỤ NỔI BẬT</h2>
          <a href="#" class="view-all">Xem tất cả <el-icon><ArrowRight /></el-icon></a>
        </div>
        
        <div class="services-grid">
          <div class="service-card" v-for="item in services" :key="item.id">
            <div class="service-icon">
              <component :is="item.icon" />
            </div>
            <div class="service-info">
              <h3>{{ item.title }}</h3>
              <p>{{ item.desc }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ================= ĐỘI NGŨ BÁC SĨ ================= -->
    <section id="doctors" class="doctors-section" v-loading="loadingDoctors">
      <div class="container">
        <div class="section-header">
          <h2 class="section-title">ĐỘI NGŨ BÁC SĨ</h2>
          <a href="#" class="view-all">Xem tất cả <el-icon><ArrowRight /></el-icon></a>
        </div>

        <div class="doctors-grid" v-if="doctorsList.length > 0">
          <div class="doctor-card" v-for="doc in doctorsList" :key="doc.id">
            <div class="doc-avatar-box">
              <img
                    class="doctor-photo"
                    src="https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?w=300"
                    alt="Doctor"
                />
            </div>
            <div class="doc-info">
              <h3>{{ doc.user?.name || doc.name || `Bác sĩ #${doc.id}` }}</h3>
              <span class="doc-spec">Chứng chỉ: {{ doc.license_number || 'N/A' }}</span>
              <p class="doc-exp">{{ doc.bio || 'Chưa cập nhật tiểu sử' }}</p>
              
              <el-button type="primary" plain size="small" class="btn-book-doc" @click="openBookingDialog(doc.id)">
                Đặt lịch khám ngay
              </el-button>
            </div>
          </div>
        </div>

        <el-empty v-else description="Chưa có dữ liệu bác sĩ trong hệ thống" />
      </div>
    </section>


    <!-- ================= QUY TRÌNH ĐẶT LỊCH ================= -->
    <section class="booking-flow">
      <div class="container">
        <div class="section-header">
          <h2 class="section-title">QUY TRÌNH ĐẶT LỊCH</h2>
        </div>
        <div class="flow-grid">
          <div class="flow-item"><span>①</span><h3>Chọn chuyên khoa</h3></div>
          <div class="flow-item"><span>②</span><h3>Chọn bác sĩ</h3></div>
          <div class="flow-item"><span>③</span><h3>Chọn thời gian</h3></div>
          <div class="flow-item"><span>④</span><h3>Xác nhận</h3></div>
        </div>
      </div>
    </section>

    <!-- ================= FOOTER ================= -->
    <footer class="site-footer">
      <div class="container footer-container">
        <div class="footer-brand">
          <div class="logo">
            <el-icon class="logo-icon"><FirstAidKit /></el-icon>
            <div class="logo-text">
              <span class="brand-name">CLINIC</span>
              <span class="brand-sub">MANAGEMENT</span>
            </div>
          </div>
          <p class="footer-desc">Hệ thống chăm sóc sức khỏe toàn diện, uy tín và tận tâm hàng đầu.</p>
        </div>
        <div class="footer-copyright">
          © 2026 Clinic Management System. All rights reserved.
        </div>
      </div>
    </footer>

    <!-- ================= POPUP ĐẶT LỊCH NHANH ================= -->
    <el-dialog 
      v-model="bookingDialogVisible" 
      title="ĐĂNG KÝ LỊCH KHÁM TRỰC TUYẾN" 
      width="500px"
      class="booking-dialog"
      destroy-on-close
    >
      <el-form :model="bookingForm" :rules="bookingRules" ref="bookingFormRef" label-position="top">
        <el-form-item label="Họ và tên bệnh nhân" prop="patient_name">
          <el-input v-model="bookingForm.patient_name" placeholder="Nhập họ và tên đầy đủ..." size="large" />
        </el-form-item>

        <el-form-item label="Số điện thoại liên hệ" prop="phone">
          <el-input v-model="bookingForm.phone" placeholder="Nhập số điện thoại..." size="large" />
        </el-form-item>

        <el-form-item label="Chọn Bác sĩ phụ trách" prop="doctor_id">
          <el-select v-model="bookingForm.doctor_id" placeholder="Chọn bác sĩ khám..." size="large" style="width: 100%;" filterable>
            <el-option
              v-for="doc in doctorsList"
              :key="doc.id"
              :label="doc.user?.name || doc.name || `Bác sĩ #${doc.id}`"
              :value="doc.id"
            />
          </el-select>
        </el-form-item>

        <el-form-item label="Thời gian dự kiến khám" prop="appointment_date">
          <el-date-picker
            v-model="bookingForm.appointment_date"
            type="datetime"
            placeholder="Chọn ngày và giờ khám..."
            size="large"
            style="width: 100%;"
            value-format="YYYY-MM-DD HH:mm:ss"
          />
        </el-form-item>
      </el-form>

      <template #footer>
        <span class="dialog-footer">
          <el-button @click="bookingDialogVisible = false" size="large">Hủy bỏ</el-button>
          <el-button type="primary" :loading="isSubmitting" @click="submitBooking" size="large" class="btn-confirm-book">
            Xác nhận đặt lịch
          </el-button>
        </span>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { 
  FirstAidKit, Phone, Calendar, User, Medal, Monitor, Service, Wallet, 
  ArrowRight, Odometer, Opportunity, UserFilled, Scissor, Female, MagicStick
} from '@element-plus/icons-vue';
import { ElMessage } from 'element-plus';

const router = useRouter();

const services = ref([
  { id: 1, title: 'Khám tổng quát', desc: 'Kiểm tra sức khỏe tổng quát, phát hiện sớm bệnh lý', icon: Odometer },
  { id: 2, title: 'Tim mạch', desc: 'Khám, tư vấn và điều trị các bệnh lý về tim mạch', icon: Opportunity },
  { id: 3, title: 'Nhi khoa', desc: 'Chăm sóc sức khỏe toàn diện cho trẻ em', icon: UserFilled },
  { id: 4, title: 'Răng hàm mặt', desc: 'Khám và điều trị các vấn đề về răng miệng', icon: Scissor },
  { id: 5, title: 'Sản phụ khoa', desc: 'Khám và chăm sóc sức khỏe sinh sản nữ giới', icon: Female },
  { id: 6, title: 'Xét nghiệm', desc: 'Xét nghiệm máu, nước tiểu, chẩn đoán hình ảnh', icon: MagicStick }
]);

const doctorsList = ref([]);
const loadingDoctors = ref(false);

const bookingDialogVisible = ref(false);
const isSubmitting = ref(false);
const bookingFormRef = ref(null);

const bookingForm = reactive({
  patient_name: '',
  phone: '',
  doctor_id: '',
  appointment_date: ''
});

const bookingRules = {
  patient_name: [{ required: true, message: 'Vui lòng nhập họ tên', trigger: 'blur' }],
  phone: [{ required: true, message: 'Vui lòng nhập số điện thoại', trigger: 'blur' }],
  doctor_id: [{ required: true, message: 'Vui lòng chọn bác sĩ', trigger: 'change' }],
  appointment_date: [{ required: true, message: 'Vui lòng chọn thời gian khám', trigger: 'change' }]
};

const goToLogin = () => {
  router.push('/login');
};

const fetchDoctorsFromDB = async () => {
  loadingDoctors.value = true;
  try {
    const response = await axios.get('/api/doctors');
    const resData = response.data.data || response.data;
    doctorsList.value = Array.isArray(resData) ? resData : (resData.items || []);
  } catch (error) {
    console.error('Không thể tải danh sách bác sĩ:', error);
  } finally {
    loadingDoctors.value = false;
  }
};

const openBookingDialog = (doctorId = '') => {
  bookingForm.patient_name = '';
  bookingForm.phone = '';
  bookingForm.doctor_id = doctorId;
  bookingForm.appointment_date = '';
  bookingDialogVisible.value = true;
};

const submitBooking = async () => {
  if (!bookingFormRef.value) return;
  await bookingFormRef.value.validate(async (valid) => {
    if (valid) {
      isSubmitting.value = true;
      try {
        await axios.post('/api/appointments', {
          ...bookingForm,
          status: 'PENDING'
        });
        
        ElMessage.success('Đặt lịch thành công! Phòng khám sẽ liên hệ lại với bạn sớm nhất.');
        bookingDialogVisible.value = false;
      } catch (error) {
        const errorMsg = error.response?.data?.message || 'Có lỗi xảy ra, vui lòng thử lại sau!';
        ElMessage.error(errorMsg);
      } finally {
        isSubmitting.value = false;
      }
    }
  });
};

const getAvatarLetter = (name) => name ? name.charAt(0).toUpperCase() : 'D';
const scrollToDoctors = () => { document.getElementById('doctors').scrollIntoView({ behavior: 'smooth' }); };

onMounted(() => {
  fetchDoctorsFromDB();
});
</script>

<style scoped>
/* Toàn bộ style chuẩn Clean Medical cho Trang chủ */
.landing-page { font-family: 'Plus Jakarta Sans', system-ui, sans-serif; background-color: #f8fafc; color: #1e293b; }
.container { max-width: 1280px; margin: 0 auto; padding: 0 24px; }
.section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; }
.section-title { font-size: 20px; font-weight: 800; color: #0f172a; margin: 0; }
.view-all { display: flex; align-items: center; gap: 6px; color: #0ea5e9; text-decoration: none; font-weight: 600; font-size: 14px; transition: all 0.2s; }
.view-all:hover { color: #0284c7; }

.navbar { background: white; box-shadow: 0 2px 15px rgba(0, 0, 0, 0.04); position: sticky; top: 0; z-index: 100; }
.nav-container { height: 80px; display: flex; align-items: center; justify-content: space-between; }
.logo { display: flex; align-items: center; gap: 12px; }
.logo-icon { font-size: 36px; color: #0ea5e9; }
.logo-text { display: flex; flex-direction: column; }
.brand-name { font-size: 18px; font-weight: 800; color: #0f172a; line-height: 1.2; }
.brand-sub { font-size: 11px; font-weight: 700; color: #0ea5e9; letter-spacing: 1px; }

.nav-links { display: flex; gap: 32px; }
.nav-links a { text-decoration: none; color: #64748b; font-weight: 600; font-size: 15px; transition: 0.3s; padding: 8px 0; }
.nav-links a:hover, .nav-links a.active { color: #0ea5e9; border-bottom: 3px solid #0ea5e9; }

.nav-actions { display: flex; align-items: center; gap: 16px; }
.hotline { display: flex; align-items: center; gap: 8px; color: #0ea5e9; font-weight: 700; font-size: 16px; margin-right: 12px; }
.btn-login { border-color: #cbd5e1; font-weight: 600; border-radius: 8px; color: #475569; }
.btn-book { background-color: #0ea5e9; border: none; font-weight: 600; border-radius: 8px; }

.hero-section { padding: 60px 0; background: linear-gradient(135deg, #ffffff 0%, #f0f9ff 100%); border-bottom: 1px solid #e0f2fe; }
.hero-container { display: flex; align-items: center; gap: 60px; }
.hero-content { flex: 1; }
.hero-title { font-size: 52px; font-weight: 800; color: #0f172a; line-height: 1.2; margin-bottom: 24px; }
.highlight { color: #0ea5e9; }
.hero-desc { font-size: 16px; color: #475569; line-height: 1.7; margin-bottom: 40px; max-width: 90%; }
.hero-buttons { display: flex; gap: 16px; margin-bottom: 40px; }
.btn-primary { background-color: #0ea5e9; border-radius: 12px; padding: 0 32px; font-weight: 700; height: 48px; border: none; box-shadow: 0 8px 20px rgba(14, 165, 233, 0.3); }
.btn-secondary { border-radius: 12px; padding: 0 32px; font-weight: 600; color: #0ea5e9; border-color: #0ea5e9; height: 48px; }
.hero-badges { display: flex; gap: 20px; flex-wrap: wrap; }
.badge-item { display: flex; align-items: center; gap: 8px; color: #475569; font-weight: 600; font-size: 14px; }
.badge-item .el-icon { color: #0ea5e9; font-size: 18px; background: white; padding: 8px; border-radius: 50%; box-shadow: 0 4px 12px rgba(0,0,0,0.06); }

.hero-image { flex: 1; position: relative; }
.hero-image img { width: 100%; border-radius: 24px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); object-fit: cover; }
.floating-card { position: absolute; top: 40px; right: -20px; background: white; padding: 16px 24px; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 12px; }
.float-icon { font-size: 32px; color: #0ea5e9; }
.floating-card strong { color: #0f172a; font-size: 18px; display: block; }
.floating-card p { margin: 0; font-size: 11px; color: #64748b; font-weight: 600; }

.services-section { padding: 80px 0; background-color: #ffffff; }
.services-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
.service-card { display: flex; align-items: flex-start; gap: 16px; padding: 24px; background: #f8fafc; border: 1px solid #f1f5f9; border-radius: 16px; transition: all 0.3s; cursor: pointer; }
.service-card:hover { transform: translateY(-4px); background: #ffffff; box-shadow: 0 12px 24px rgba(0,0,0,0.04); border-color: #e0f2fe; }
.service-icon { font-size: 28px; color: #0ea5e9; background: #e0f2fe; padding: 16px; border-radius: 16px; display: flex; }
.service-info h3 { margin: 0 0 8px 0; font-size: 16px; font-weight: 700; color: #0f172a; }
.service-info p { margin: 0; font-size: 13px; color: #64748b; line-height: 1.5; }

.doctors-section { padding: 80px 0; background-color: #f8fafc; min-height: 400px; }
.doctors-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; }
.doctor-card { background: #ffffff; border-radius: 20px; padding: 24px 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); text-align: center; transition: all 0.3s; border: 1px solid #f1f5f9; }
.doctor-card:hover { transform: translateY(-8px); box-shadow: 0 15px 30px rgba(0,0,0,0.08); }
.doc-avatar-box { display: flex; justify-content: center; margin-bottom: 16px; }
.doc-avatar-letter { background: linear-gradient(135deg, #0ea5e9, #0284c7); color: white; font-weight: 800; font-size: 28px; box-shadow: 0 4px 12px rgba(14, 165, 233, 0.3); }
.doc-info h3 { margin: 0 0 6px 0; font-size: 16px; font-weight: 700; color: #0f172a; }
.doc-spec { display: block; font-size: 13px; font-weight: 600; color: #0ea5e9; margin-bottom: 4px; }
.doc-exp { font-size: 12px; color: #64748b; margin: 0 0 16px 0; min-height: 32px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.btn-book-doc { width: 100%; border-radius: 8px; font-weight: 600; }

.site-footer { background-color: #ffffff; padding: 40px 0; border-top: 1px solid #e2e8f0; }
.footer-container { display: flex; justify-content: space-between; align-items: center; }
.footer-desc { font-size: 14px; color: #64748b; margin-top: 12px; max-width: 300px; line-height: 1.5; }
.footer-copyright { font-size: 13px; color: #94a3b8; font-weight: 500; }

.booking-dialog :deep(.el-dialog__header) { padding: 20px 24px; border-bottom: 1px solid #ebeef5; margin-right: 0; }
.booking-dialog :deep(.el-dialog__title) { font-weight: 800; color: #0f172a; font-size: 18px; }
.booking-dialog :deep(.el-dialog__body) { padding: 24px; }
.btn-confirm-book { background-color: #0ea5e9; border: none; font-weight: 700; border-radius: 8px; width: 100%; }

.hero-stats{position:absolute;left:25px;bottom:-30px;display:flex;gap:15px;}
.stat-card{background:white;padding:18px 20px;border-radius:16px;text-align:center;box-shadow:0 12px 30px rgba(15,23,42,.08);}
.stat-card strong{display:block;color:#0ea5e9;font-size:24px;font-weight:800;}
.stat-card span{color:#64748b;font-size:13px;}
.doctor-photo{width:90px;height:90px;border-radius:50%;object-fit:cover;border:4px solid #dbeafe;}
.booking-flow{padding:80px 0;background:white;}
.flow-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:24px;}
.flow-item{background:#f8fafc;border-radius:18px;padding:28px;text-align:center;transition:.3s;}
.flow-item span{display:inline-flex;width:50px;height:50px;border-radius:50%;background:#0ea5e9;color:white;align-items:center;justify-content:center;font-weight:800;margin-bottom:14px;}
.flow-item:hover{transform:translateY(-6px);}
@media(max-width:992px){.hero-container{flex-direction:column}.services-grid{grid-template-columns:repeat(2,1fr)}.doctors-grid{grid-template-columns:repeat(2,1fr)}.flow-grid{grid-template-columns:repeat(2,1fr)}.hero-stats{position:static;margin-top:25px}}
@media(max-width:640px){.nav-links{display:none}.hero-title{font-size:38px}.services-grid,.doctors-grid,.flow-grid{grid-template-columns:1fr}.hero-buttons{flex-direction:column}}
</style>