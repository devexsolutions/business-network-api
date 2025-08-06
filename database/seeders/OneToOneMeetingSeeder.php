<?php

namespace Database\Seeders;

use App\Models\OneToOneMeeting;
use App\Models\ReferralCard;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class OneToOneMeetingSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        
        if ($users->count() < 2) {
            return;
        }

        // Crear algunas reuniones de ejemplo
        $meetings = [
            [
                'requester_id' => $users[0]->id,
                'requested_id' => $users[1]->id,
                'meeting_date' => now()->addDays(3)->setTime(10, 0),
                'location' => 'Oficina TechCorp, Sala de Reuniones A',
                'meeting_type' => 'in_person',
                'status' => 'pending',
                'purpose' => 'Discutir oportunidades de colaboración en proyectos de transformación digital',
                'agenda' => 'Presentación de servicios, identificación de sinergias, propuesta de colaboración',
                'priority' => 'high',
                'contact_info' => [
                    'phone' => '+34 91 123 4567',
                    'email' => 'juan@techcorp.com'
                ]
            ],
            [
                'requester_id' => $users[1]->id,
                'requested_id' => $users[2]->id,
                'meeting_date' => now()->addDays(5)->setTime(15, 30),
                'confirmed_date' => now()->addDays(5)->setTime(15, 30),
                'location' => 'Café Central, Valencia',
                'meeting_type' => 'in_person',
                'status' => 'accepted',
                'purpose' => 'Intercambio de contactos y referencias de marketing digital',
                'agenda' => 'Networking, intercambio de tarjetas, discusión de casos de éxito',
                'priority' => 'medium',
                'accepted_at' => now()->subDays(1),
                'contact_info' => [
                    'phone' => '+34 93 234 5678',
                    'email' => 'maria@techcorp.com'
                ]
            ],
            [
                'requester_id' => $users[2]->id,
                'requested_id' => $users[0]->id,
                'meeting_date' => now()->subDays(2)->setTime(11, 0),
                'confirmed_date' => now()->subDays(2)->setTime(11, 0),
                'location' => 'Zoom Meeting',
                'meeting_type' => 'virtual',
                'status' => 'completed',
                'purpose' => 'Presentación de servicios de marketing para startups tecnológicas',
                'agenda' => 'Análisis de necesidades, propuesta de servicios, definición de próximos pasos',
                'priority' => 'high',
                'accepted_at' => now()->subDays(5),
                'completed_at' => now()->subDays(2)->setTime(12, 0),
                'requester_notes' => 'Excelente reunión, identificamos varias oportunidades de colaboración',
                'requested_notes' => 'Muy interesante propuesta, evaluaremos internamente',
                'contact_info' => [
                    'phone' => '+34 96 345 6789',
                    'email' => 'carlos@digitalmarketingpro.com'
                ]
            ]
        ];

        foreach ($meetings as $meetingData) {
            $meeting = OneToOneMeeting::create($meetingData);

            // Crear fichas de referencia para la reunión completada
            if ($meeting->status === 'completed') {
                $this->createReferralCards($meeting);
            }
        }
    }

    private function createReferralCards(OneToOneMeeting $meeting)
    {
        $referrals = [
            [
                'meeting_id' => $meeting->id,
                'from_user_id' => $meeting->requester_id,
                'to_user_id' => $meeting->requested_id,
                'referral_date' => $meeting->completed_at->toDateString(),
                'referral_description' => 'Empresa de desarrollo de software especializada en e-commerce. Buscan soluciones de marketing digital para aumentar conversiones.',
                'referral_type' => 'external',
                'referral_status' => ['card_delivered' => true, 'told_to_call' => true],
                'contact_name' => 'Ana Martínez',
                'contact_phone' => '+34 91 555 0123',
                'contact_email' => 'ana.martinez@ecommercesolutions.com',
                'contact_address' => 'Calle Serrano 45, 28001 Madrid',
                'comments' => 'Muy interesada en servicios de SEO y SEM. Presupuesto disponible para Q1.',
                'interest_level' => 'very_high',
                'status' => 'sent',
                'sent_at' => $meeting->completed_at,
                'follow_up_actions' => [
                    'call_within_week' => true,
                    'send_portfolio' => true,
                    'schedule_presentation' => true
                ]
            ],
            [
                'meeting_id' => $meeting->id,
                'from_user_id' => $meeting->requested_id,
                'to_user_id' => $meeting->requester_id,
                'referral_date' => $meeting->completed_at->toDateString(),
                'referral_description' => 'Startup fintech que necesita desarrollo de aplicación móvil para gestión de inversiones.',
                'referral_type' => 'external',
                'referral_status' => ['card_delivered' => true],
                'contact_name' => 'Roberto Silva',
                'contact_phone' => '+34 93 444 0987',
                'contact_email' => 'roberto@fintechinvest.com',
                'contact_address' => 'Passeig de Gràcia 123, 08008 Barcelona',
                'comments' => 'Proyecto urgente, necesitan comenzar desarrollo en febrero. Presupuesto considerable.',
                'interest_level' => 'high',
                'status' => 'received',
                'sent_at' => $meeting->completed_at,
                'received_at' => $meeting->completed_at->addHours(2),
                'follow_up_actions' => [
                    'technical_meeting' => true,
                    'prepare_proposal' => true
                ]
            ]
        ];

        foreach ($referrals as $referralData) {
            ReferralCard::create($referralData);
        }
    }
}