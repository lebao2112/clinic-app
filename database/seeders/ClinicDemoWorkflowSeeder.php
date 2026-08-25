<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Specialty;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Medicine;
use App\Models\Appointment;
use App\Models\Examination;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Invoice;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ClinicDemoWorkflowSeeder extends Seeder
{
    public function run(): void
    {
        Invoice::query()->delete();
        PrescriptionItem::query()->delete();
        Prescription::query()->delete();
        Examination::query()->delete();
        Appointment::query()->delete();

        $doctorRole = Role::where('name', 'DOCTOR')->first();
        $doctorRoleId = $doctorRole ? $doctorRole->id : 3;

        $specialtyNames = [
            'Nội Tổng Quát', 'Nhi Khoa', 'Tim Mạch', 'Da Liễu', 
            'Thần Kinh', 'Chấn Thương Chỉnh Hình', 'Mắt', 'Tai Mũi Họng', 
            'Răng Hàm Mặt', 'Y Học Cổ Truyền'
        ];
        $specialties = [];
        foreach ($specialtyNames as $name) {
            $specialties[] = Specialty::updateOrCreate(
                ['name' => $name],
                ['description' => 'Chuyên khoa chuyên sâu về điều trị và tư vấn các bệnh lý liên quan đến ' . mb_strtolower($name, 'UTF-8') . '.']
            );
        }

        $doctors = [];
        $doctorNames = [
            'Nguyễn Hải Nam', 'Trần Văn Hoàng', 'Lê Thị Thu Thủy', 'Phạm Minh Quân', 
            'Hoàng Văn Hùng', 'Vũ Thị Lan', 'Đỗ Thành Long', 'Bùi Thị Hoa', 
            'Ngô Văn Tuấn', 'Dương Thị Mai'
        ];
        foreach ($doctorNames as $index => $docName) {
            $user = User::updateOrCreate(
                ['email' => 'doctor.vn' . ($index + 1) . '@clinic.com'],
                [
                    'name' => 'Bs. ' . $docName,
                    'password' => Hash::make('password123'),
                    'role_id' => $doctorRoleId,
                ]
            );

            $doctors[] = Doctor::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'specialty_id' => $specialties[array_rand($specialties)]->id,
                    'license_number' => 'CCHN-1000' . $index,
                    'bio' => 'Bác sĩ chuyên khoa giàu kinh nghiệm.',
                ]
            );
        }

        $patientNames = [
            'Nguyễn Văn Bảy', 'Trần Thị Mai', 'Lê Hoàng Long', 'Phạm Thị Nga', 
            'Hoàng Văn Minh', 'Vũ Thị Thanh', 'Đỗ Văn Nam', 'Bùi Thị Dung', 
            'Ngô Văn Phúc', 'Dương Thị Lan', 'Lý Văn Hùng', 'Hồ Thị Thảo', 
            'Đinh Văn Mạnh', 'Mai Thị Ngọc', 'Đoàn Văn Kiên'
        ];
        $provinces = [
            'Hà Nội', 'TP. Hồ Chí Minh', 'Đà Nẵng', 'Hải Phòng', 'Cần Thơ',
            'Bắc Ninh', 'Quảng Ninh', 'Thanh Hóa', 'Nghệ An', 'Thừa Thiên Huế'
        ];

        $patients = [];
        foreach ($patientNames as $index => $patientName) {
            $randomProvince = $provinces[array_rand($provinces)];
            $patients[] = Patient::updateOrCreate(
                ['email' => 'patient.vn' . ($index + 1) . '@example.com'],
                [
                    'phone' => '090' . rand(1000000, 9999999),
                    'code' => 'BN-' . (100000 + $index),
                    'full_name' => $patientName,
                    'gender' => $index % 2 == 0 ? 'male' : 'female',
                    'date_of_birth' => rand(1960, 2002) . '-' . rand(1, 12) . '-' . rand(1, 28),
                    'address' => 'Số ' . rand(1, 500) . ', ' . $randomProvince,
                ]
            );
        }

        $medicineList = [
            ['code' => 'MED-301', 'name' => 'Paracetamol 500mg', 'unit' => 'Viên', 'price' => 5000],
            ['code' => 'MED-302', 'name' => 'Amoxicillin 500mg', 'unit' => 'Viên', 'price' => 12000],
            ['code' => 'MED-303', 'name' => 'Ibuprofen 400mg', 'unit' => 'Viên', 'price' => 8000],
            ['code' => 'MED-304', 'name' => 'Aspirin 81mg', 'unit' => 'Viên', 'price' => 4000],
            ['code' => 'MED-305', 'name' => 'Vitamin C 1000mg', 'unit' => 'Ống', 'price' => 15000],
            ['code' => 'MED-306', 'name' => 'Omeprazole 20mg', 'unit' => 'Viên', 'price' => 10000],
            ['code' => 'MED-307', 'name' => 'Metformin 850mg', 'unit' => 'Viên', 'price' => 6000],
            ['code' => 'MED-308', 'name' => 'Cetirizine 10mg', 'unit' => 'Viên', 'price' => 5000],
            ['code' => 'MED-309', 'name' => 'Azithromycin 250mg', 'unit' => 'Viên', 'price' => 18000],
            ['code' => 'MED-310', 'name' => 'Salbutamol 2mg', 'unit' => 'Viên', 'price' => 3000],
        ];

        $medicines = [];
        foreach ($medicineList as $medData) {
            $medicines[] = Medicine::updateOrCreate(
                ['code' => $medData['code']],
                [
                    'name' => $medData['name'],
                    'unit' => $medData['unit'],
                    'price' => $medData['price'],
                    'stock' => rand(200, 1000),
                    'is_active' => true,
                ]
            );
        }

        $reasons = ['Khám tổng quát', 'Đau đầu kéo dài', 'Ho khan, sốt nhẹ', 'Đau tức ngực', 'Đau nhức xương khớp'];
        $diagnoses = ['Viêm họng cấp', 'Rối loạn tiền đình', 'Viêm loét dạ dày', 'Đau cơ xương khớp', 'Huyết áp ổn định'];

        for ($i = 1; $i <= 12; $i++) {
            $patient = $patients[array_rand($patients)];
            $doctor = $doctors[array_rand($doctors)];

            $appointment = Appointment::create([
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'scheduled_at' => now()->subDays(rand(1, 10))->addHours(rand(8, 16)),
                'status' => 'completed',
                'reason' => $reasons[array_rand($reasons)],
            ]);

            $examination = Examination::create([
                'appointment_id' => $appointment->id,
                'doctor_id' => $doctor->id,
                'patient_id' => $patient->id,
                'diagnosis' => $diagnoses[array_rand($diagnoses)],
                'notes' => 'Bệnh nhân cần uống thuốc đúng giờ.',
                'examined_at' => now(),
            ]);

            $prescription = Prescription::create([
                'examination_id' => $examination->id,
                'doctor_id' => $doctor->id,
                'notes' => 'Uống nhiều nước ấm.',
            ]);

            $selectedMedicines = (array) array_rand($medicines, rand(2, 3));
            $medicineTotal = 0;

            foreach ($selectedMedicines as $medIndex) {
                $med = $medicines[$medIndex];
                $qty = rand(5, 20);
                $medicineTotal += ($qty * $med->price);

                PrescriptionItem::create([
                    'prescription_id' => $prescription->id,
                    'medicine_id' => $med->id,
                    'quantity' => $qty,
                    'dosage' => rand(1, 3) . ' ' . $med->unit . '/lần, ngày 2 lần',
                    'usage_instruction' => 'Uống sau bữa ăn chính',
                ]);
            }

            $examinationFee = 100000;
            $subtotal = $examinationFee + $medicineTotal;
            $total = max($subtotal - 20000, 0);

            Invoice::create([
                'examination_id' => $examination->id,
                'invoice_code' => 'INV-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5)),
                'subtotal' => $subtotal,
                'discount' => 20000,
                'total' => $total,
                'status' => rand(0, 1) == 1 ? 'paid' : 'unpaid',
                'issued_at' => now(),
            ]);
        }
    }
}