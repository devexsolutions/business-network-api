<?php

namespace Database\Seeders;

use App\Models\BusinessRecommendation;
use App\Models\OneToOneFollowUp;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class BusinessRecommendationSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        
        if ($users->count() < 3) {
            return;
        }

        // Crear recomendaciones de negocio
        $recommendations = [
            [
                'recommender_id' => $users[0]->id, // Juan recomienda
                'recommended_to_id' => $users[1]->id, // a María
                'recommended_user_id' => $users[2]->id, // que contacte a Carlos
                'recommendation_date' => now()->subDays(5),
                'business_description' => 'Carlos es experto en marketing digital y puede ayudar con la estrategia de crecimiento de TechCorp. Tiene experiencia trabajando con empresas tecnológicas similares.',
                'why_recommended' => 'He trabajado con Carlos en varios proyectos y su expertise en SEO y SEM es excepcional. Creo que puede aportar mucho valor a vuestros objetivos de crecimiento.',
                'contact_info' => [
                    'phone' => '+34 96 345 6789',
                    'email' => 'carlos@digitalmarketingpro.com',
                    'linkedin' => 'https://linkedin.com/in/carlos-lopez-marketing'
                ],
                'recommendation_type' => 'service_provider',
                'priority_level' => 'high',
                'tags' => ['marketing', 'SEO', 'crecimiento', 'tecnología'],
                'estimated_value' => 15000.00,
                'status' => 'contacted',
                'contacted_at' => now()->subDays(2),
                'follow_up_notes' => 'María contactó a Carlos y tuvieron una primera reunión muy positiva.',
            ],
            [
                'recommender_id' => $users[1]->id, // María recomienda
                'recommended_to_id' => $users[2]->id, // a Carlos
                'recommended_user_id' => $users[0]->id, // que contacte a Juan
                'recommendation_date' => now()->subDays(3),
                'business_description' => 'Juan lidera TechCorp y están buscando expandir sus servicios de desarrollo. Podrían necesitar una estrategia de marketing para su nueva línea de productos.',
                'why_recommended' => 'Juan es un CEO visionario con un equipo técnico sólido. Están en fase de crecimiento y necesitan expertise en marketing digital.',
                'contact_info' => [
                    'phone' => '+34 91 123 4567',
                    'email' => 'juan@techcorp.com',
                    'website' => 'https://techcorp.com'
                ],
                'recommendation_type' => 'potential_client',
                'priority_level' => 'urgent',
                'tags' => ['cliente potencial', 'tecnología', 'CEO', 'crecimiento'],
                'estimated_value' => 25000.00,
                'status' => 'business_done',
                'contacted_at' => now()->subDays(1),
                'completed_at' => now(),
                'outcome_notes' => 'Excelente resultado! Carlos cerró un contrato de 6 meses con TechCorp para su estrategia de marketing digital.',
            ],
            [
                'recommender_id' => $users[2]->id, // Carlos recomienda
                'recommended_to_id' => $users[0]->id, // a Juan
                'recommended_user_id' => $users[1]->id, // que contacte a María
                'recommendation_date' => now()->subDays(1),
                'business_description' => 'María es una desarrolladora excepcional con experiencia en arquitectura de software. Podría ser perfecta para liderar el nuevo proyecto de aplicación móvil.',
                'why_recommended' => 'He visto el trabajo de María y su expertise técnica es impresionante. Además, tiene experiencia en Flutter que es justo lo que necesitáis.',
                'contact_info' => [
                    'phone' => '+34 93 234 5678',
                    'email' => 'maria@techcorp.com',
                    'skills' => ['Laravel', 'Flutter', 'Arquitectura Software']
                ],
                'recommendation_type' => 'partnership',
                'priority_level' => 'medium',
                'tags' => ['desarrollo', 'Flutter', 'arquitectura', 'liderazgo técnico'],
                'estimated_value' => 8000.00,
                'status' => 'pending',
                'is_mutual' => true,
            ]
        ];

        foreach ($recommendations as $recommendationData) {
            BusinessRecommendation::create($recommendationData);
        }

        // Crear seguimientos uno a uno
        $followUps = [
            [
                'user_id' => $users[0]->id,
                'met_with_user_id' => $users[1]->id,
                'invited_by_user_id' => null,
                'group_name' => 'BNI EXS Supera',
                'location' => 'Café Central Madrid',
                'meeting_date' => now()->subDays(7),
                'conversation_topics' => 'Discutimos las oportunidades de colaboración entre nuestras empresas, intercambiamos experiencias sobre gestión de equipos técnicos y hablamos sobre las tendencias del mercado tecnológico.',
                'meeting_type' => 'coffee_chat',
                'duration_minutes' => 90,
                'outcome' => 'excellent',
                'follow_up_actions' => 'Programar reunión con equipos técnicos, compartir casos de estudio, evaluar proyecto conjunto',
                'business_opportunities' => 'Posible colaboración en proyecto de transformación digital para cliente del sector financiero. Presupuesto estimado: 50.000€',
                'referrals_given' => 'Recomendé a María el contacto de Carlos López para servicios de marketing digital',
                'referrals_received' => 'María me recomendó a Ana García, especialista en UX/UI para nuestros proyectos',
                'future_meeting_planned' => true,
                'next_meeting_date' => now()->addDays(14),
                'notes' => 'Reunión muy productiva. María demostró gran conocimiento técnico y visión estratégica. Definitivamente hay potencial para trabajar juntos.',
                'status' => 'follow_up_pending',
            ],
            [
                'user_id' => $users[1]->id,
                'met_with_user_id' => $users[2]->id,
                'invited_by_user_id' => $users[0]->id,
                'group_name' => 'BNI EXS Supera',
                'location' => 'Oficina StartupHub Barcelona',
                'meeting_date' => now()->subDays(4),
                'conversation_topics' => 'Hablamos sobre estrategias de marketing para empresas tecnológicas, casos de éxito en el sector, herramientas de automatización y métricas clave para medir ROI.',
                'attendees' => ['Carlos López', 'María García', 'Invitado: Pedro Sánchez (CEO StartupHub)'],
                'meeting_type' => 'business_lunch',
                'duration_minutes' => 120,
                'outcome' => 'good',
                'follow_up_actions' => 'Carlos enviará propuesta de servicios, programar demo de herramientas, definir KPIs del proyecto',
                'business_opportunities' => 'Contrato de marketing digital para TechCorp. Servicios de SEO, SEM y estrategia de contenidos por 6 meses.',
                'referrals_given' => 'Recomendé a Carlos el contacto de Luis Rodríguez, CEO de una fintech que necesita marketing',
                'referrals_received' => 'Carlos me recomendó a Elena Martín, especialista en automatización de procesos',
                'future_meeting_planned' => false,
                'notes' => 'Carlos demostró gran expertise en marketing digital. Su propuesta se alinea perfectamente con nuestras necesidades de crecimiento.',
                'status' => 'completed',
            ],
            [
                'user_id' => $users[2]->id,
                'met_with_user_id' => $users[0]->id,
                'invited_by_user_id' => null,
                'group_name' => 'BNI EXS Supera',
                'location' => 'Zoom Meeting',
                'meeting_date' => now()->subDays(2),
                'conversation_topics' => 'Revisamos los resultados de la campaña de marketing implementada, analizamos métricas de conversión, discutimos optimizaciones y planificamos la estrategia para el próximo trimestre.',
                'meeting_type' => 'one_to_one',
                'duration_minutes' => 60,
                'outcome' => 'excellent',
                'follow_up_actions' => 'Implementar optimizaciones acordadas, preparar informe mensual, planificar campaña Q2',
                'business_opportunities' => 'Extensión del contrato actual + nuevos servicios de marketing automation. Valor estimado: 30.000€',
                'referrals_given' => 'Recomendé a Juan el contacto de Sofía López, especialista en transformación digital',
                'referrals_received' => 'Juan me recomendó a Miguel Torres, CEO de una empresa de logística que necesita marketing',
                'future_meeting_planned' => true,
                'next_meeting_date' => now()->addDays(30),
                'notes' => 'Los resultados superaron las expectativas. Incremento del 40% en leads cualificados. Juan está muy satisfecho con el servicio.',
                'attachments' => [
                    'informe_resultados_enero.pdf',
                    'propuesta_q2_marketing.pdf'
                ],
                'status' => 'completed',
            ]
        ];

        foreach ($followUps as $followUpData) {
            OneToOneFollowUp::create($followUpData);
        }
    }
}