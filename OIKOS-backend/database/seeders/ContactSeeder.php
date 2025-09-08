<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Contact;
use Carbon\Carbon;

class ContactSeeder extends Seeder
{
    public function run(): void
    {
        $contacts = [
            [
                'name' => 'Giulia Verdi',
                'email' => 'giulia.verdi@example.com',
                'phone' => '+39 340 1234567',
                'subject' => 'Richiesta preventivo villa moderna',
                'message' => 'Buongiorno, vorrei un preventivo per la progettazione di una villa unifamiliare.',
                'status' => 'new',
            ],
            [
                'name' => 'Marco Bianchi',
                'email' => 'marco.bianchi@example.com',
                'phone' => null,
                'subject' => 'Restauro edificio storico',
                'message' => 'Salve, ho bisogno di una consulenza per il restauro di un palazzo del 1800.',
                'status' => 'read',
                'read_at' => Carbon::now()->subDays(2),
                'notes' => 'Da assegnare all’arch. Rossi',
            ],
            [
                'name' => 'Lucia Neri',
                'email' => 'lucia.neri@example.com',
                'phone' => '+39 333 9876543',
                'subject' => 'Collaborazione per concorso',
                'message' => 'Ciao, sarei interessata a collaborare con il vostro studio per un concorso di architettura.',
                'status' => 'replied',
                'read_at' => Carbon::now()->subDays(5),
                'replied_at' => Carbon::now()->subDays(4),
                'notes' => 'Risposto con proposta di call conoscitiva',
            ],
        ];

        foreach ($contacts as $contact) {
            Contact::firstOrCreate(
                [
                    'email' => $contact['email'],
                    'subject' => $contact['subject'],
                ],
                $contact
            );
        }
    }
}
