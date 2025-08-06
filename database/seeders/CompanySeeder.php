<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $companies = [
            [
                'name' => 'TechCorp Solutions',
                'slug' => 'techcorp-solutions',
                'description' => 'Empresa líder en soluciones tecnológicas empresariales',
                'company_description' => 'Somos una empresa tecnológica especializada en transformación digital, desarrollo de software y consultoría IT. Ayudamos a empresas a modernizar sus procesos mediante soluciones innovadoras.',
                'industry' => 'Tecnología',
                'size' => 'large',
                'employees_count' => 150,
                'founded_year' => 2010,
                'website' => 'https://techcorp.com',
                'email' => 'info@techcorp.com',
                'phone' => '+34 91 123 4567',
                'fax' => '+34 91 123 4568',
                'toll_free_phone' => '900 123 456',
                'tax_id' => 'A12345678',
                'tax_address' => 'Calle Tecnología 123, 28001 Madrid',
                'address' => [
                    'street' => 'Calle Tecnología 123',
                    'city' => 'Madrid',
                    'postal_code' => '28001',
                    'country' => 'España'
                ],
                'social_links' => [
                    'linkedin' => 'https://linkedin.com/company/techcorp-solutions',
                    'twitter' => 'https://twitter.com/techcorp',
                    'facebook' => 'https://facebook.com/techcorp'
                ],
                'business_hours' => [
                    'monday' => '09:00-18:00',
                    'tuesday' => '09:00-18:00',
                    'wednesday' => '09:00-18:00',
                    'thursday' => '09:00-18:00',
                    'friday' => '09:00-17:00'
                ],
                'services' => [
                    'Desarrollo de Software',
                    'Consultoría IT',
                    'Transformación Digital',
                    'Cloud Computing',
                    'Ciberseguridad'
                ],
                'membership_type' => 'premium',
                'membership_start_date' => now()->subYear(),
                'membership_end_date' => now()->addYear(),
                'membership_status' => 'active',
                'is_verified' => true,
            ],
            [
                'name' => 'StartupHub',
                'slug' => 'startuphub',
                'description' => 'Incubadora de startups innovadoras',
                'company_description' => 'Incubadora y aceleradora de startups que proporciona mentoring, financiación y recursos para emprendedores tecnológicos.',
                'industry' => 'Incubadora',
                'size' => 'medium',
                'employees_count' => 25,
                'founded_year' => 2018,
                'website' => 'https://startuphub.com',
                'email' => 'hello@startuphub.com',
                'phone' => '+34 93 234 5678',
                'tax_id' => 'B87654321',
                'tax_address' => 'Passeig de Gràcia 456, 08007 Barcelona',
                'address' => [
                    'street' => 'Passeig de Gràcia 456',
                    'city' => 'Barcelona',
                    'postal_code' => '08007',
                    'country' => 'España'
                ],
                'social_links' => [
                    'linkedin' => 'https://linkedin.com/company/startuphub',
                    'twitter' => 'https://twitter.com/startuphub'
                ],
                'business_hours' => [
                    'monday' => '10:00-19:00',
                    'tuesday' => '10:00-19:00',
                    'wednesday' => '10:00-19:00',
                    'thursday' => '10:00-19:00',
                    'friday' => '10:00-18:00'
                ],
                'services' => [
                    'Incubación de Startups',
                    'Mentoring',
                    'Financiación',
                    'Networking',
                    'Formación Empresarial'
                ],
                'membership_type' => 'enterprise',
                'membership_start_date' => now()->subMonths(8),
                'membership_end_date' => now()->addMonths(4),
                'membership_status' => 'active',
                'is_verified' => true,
            ],
            [
                'name' => 'Digital Marketing Pro',
                'slug' => 'digital-marketing-pro',
                'description' => 'Agencia de marketing digital especializada en PYMES',
                'company_description' => 'Agencia especializada en marketing digital para pequeñas y medianas empresas. Ofrecemos servicios de SEO, SEM, redes sociales y estrategias de crecimiento.',
                'industry' => 'Marketing',
                'size' => 'small',
                'employees_count' => 12,
                'founded_year' => 2020,
                'website' => 'https://digitalmarketingpro.com',
                'email' => 'contact@digitalmarketingpro.com',
                'phone' => '+34 96 345 6789',
                'toll_free_phone' => '900 456 789',
                'tax_id' => 'C11223344',
                'tax_address' => 'Calle Colón 789, 46004 Valencia',
                'address' => [
                    'street' => 'Calle Colón 789',
                    'city' => 'Valencia',
                    'postal_code' => '46004',
                    'country' => 'España'
                ],
                'social_links' => [
                    'linkedin' => 'https://linkedin.com/company/digital-marketing-pro',
                    'instagram' => 'https://instagram.com/digitalmarketingpro'
                ],
                'business_hours' => [
                    'monday' => '09:00-17:00',
                    'tuesday' => '09:00-17:00',
                    'wednesday' => '09:00-17:00',
                    'thursday' => '09:00-17:00',
                    'friday' => '09:00-15:00'
                ],
                'services' => [
                    'SEO',
                    'SEM',
                    'Redes Sociales',
                    'Content Marketing',
                    'Analytics',
                    'Growth Hacking'
                ],
                'membership_type' => 'basic',
                'membership_start_date' => now()->subMonths(3),
                'membership_end_date' => now()->addMonths(9),
                'membership_status' => 'active',
                'is_verified' => false,
            ],
        ];

        foreach ($companies as $company) {
            Company::create($company);
        }
    }
}