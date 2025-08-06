<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $companies = Company::all();

        $users = [
            [
                'name' => 'Juan Pérez',
                'email' => 'juan@example.com',
                'password' => Hash::make('password'),
                'phone' => '+34 91 123 4567',
                'direct_phone' => '+34 600 123 456',
                'fax' => '+34 91 123 4568',
                'position' => 'CEO',
                'specialty' => 'Dirección Estratégica',
                'professional_group' => 'Ejecutivos',
                'bio' => 'Emprendedor apasionado por la tecnología y la innovación.',
                'business_description' => 'Lideramos la transformación digital de empresas mediante soluciones tecnológicas innovadoras y estrategias de crecimiento sostenible.',
                'location' => 'Madrid, España',
                'tax_address' => 'Calle Gran Vía 123, 28013 Madrid',
                'tax_id' => 'B12345678',
                'tax_id_type' => 'CIF',
                'skills' => ['Liderazgo', 'Estrategia', 'Tecnología', 'Transformación Digital'],
                'interests' => ['Startups', 'IA', 'Blockchain', 'Innovación'],
                'keywords' => ['CEO', 'Liderazgo', 'Tecnología', 'Estrategia', 'Innovación'],
                'membership_status' => 'active',
                'membership_renewal_date' => now()->addYear(),
                'profile_completed' => true,
                'company_id' => $companies->first()->id,
            ],
            [
                'name' => 'María García',
                'email' => 'maria@example.com',
                'password' => Hash::make('password'),
                'phone' => '+34 93 234 5678',
                'direct_phone' => '+34 600 234 567',
                'position' => 'CTO',
                'specialty' => 'Desarrollo de Software',
                'professional_group' => 'Tecnología',
                'bio' => 'Desarrolladora full-stack con 10 años de experiencia.',
                'business_description' => 'Especialista en arquitectura de software y desarrollo de aplicaciones web y móviles con tecnologías modernas.',
                'location' => 'Barcelona, España',
                'tax_address' => 'Passeig de Gràcia 456, 08007 Barcelona',
                'tax_id' => '12345678A',
                'tax_id_type' => 'NIF',
                'skills' => ['PHP', 'Laravel', 'Vue.js', 'Flutter', 'Arquitectura Software'],
                'interests' => ['Desarrollo', 'Open Source', 'Mentoring', 'Tecnología'],
                'keywords' => ['CTO', 'Desarrollo', 'Laravel', 'Flutter', 'Arquitectura'],
                'membership_status' => 'active',
                'membership_renewal_date' => now()->addYear(),
                'profile_completed' => true,
                'company_id' => $companies->first()->id,
            ],
            [
                'name' => 'Carlos López',
                'email' => 'carlos@example.com',
                'password' => Hash::make('password'),
                'phone' => '+34 96 345 6789',
                'direct_phone' => '+34 600 345 678',
                'toll_free_phone' => '900 123 456',
                'position' => 'Marketing Director',
                'specialty' => 'Marketing Digital',
                'professional_group' => 'Marketing',
                'bio' => 'Especialista en marketing digital y growth hacking.',
                'business_description' => 'Experto en estrategias de marketing digital, SEO, SEM y growth hacking para empresas en crecimiento.',
                'location' => 'Valencia, España',
                'tax_address' => 'Calle Colón 789, 46004 Valencia',
                'tax_id' => '87654321B',
                'tax_id_type' => 'NIF',
                'skills' => ['Marketing Digital', 'SEO', 'Analytics', 'Growth Hacking', 'SEM'],
                'interests' => ['Growth Hacking', 'Networking', 'Eventos', 'Emprendimiento'],
                'keywords' => ['Marketing', 'SEO', 'Growth Hacking', 'Digital', 'Analytics'],
                'membership_status' => 'active',
                'membership_renewal_date' => now()->addMonths(6),
                'profile_completed' => true,
                'company_id' => $companies->skip(1)->first()->id,
            ],
        ];

        foreach ($users as $userData) {
            User::create($userData);
        }
    }
}