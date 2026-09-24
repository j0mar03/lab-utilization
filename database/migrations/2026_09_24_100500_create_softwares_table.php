<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('softwares', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('short')->nullable();
            $table->string('icon', 30)->default('💻');
            $table->string('category')->nullable();
            $table->text('description')->nullable();
            $table->json('departments')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // Seed initial catalog from default set
        $initialSoftware = [
            [
                'name'        => 'Adobe Creative Cloud',
                'short'       => 'Adobe CC',
                'icon'        => '🎨',
                'category'    => 'Design & Multimedia',
                'description' => 'Photoshop, Premiere Pro, Illustrator, InDesign, After Effects',
                'departments' => json_encode(['DOMIT', 'DECET']),
                'is_active'   => true,
                'sort_order'  => 1,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'name'        => 'Huawei eNSP',
                'short'       => 'Huawei eNSP',
                'icon'        => '🌐',
                'category'    => 'Networking Simulation',
                'description' => 'Enterprise Network Simulation Platform (Routers, Switches, Firewalls, WLAN)',
                'departments' => json_encode(['DECET']),
                'is_active'   => true,
                'sort_order'  => 2,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'name'        => 'Microsoft Office 365',
                'short'       => 'MS Office 365',
                'icon'        => '📄',
                'category'    => 'Office Productivity',
                'description' => 'Word, Excel, PowerPoint, Access, Teams, Outlook',
                'departments' => json_encode(['DOMIT', 'DEMET', 'DECET']),
                'is_active'   => true,
                'sort_order'  => 3,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'name'        => 'Engineering Software',
                'short'       => 'Engineering CAD',
                'icon'        => '📐',
                'category'    => 'CAD & Simulation',
                'description' => 'AutoCAD, SolidWorks, MATLAB, Proteus, NI Multisim, FluidSIM',
                'departments' => json_encode(['DEMET', 'DECET']),
                'is_active'   => true,
                'sort_order'  => 4,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'name'        => 'Programming & IDEs',
                'short'       => 'Programming / IDEs',
                'icon'        => '💻',
                'category'    => 'Software Development',
                'description' => 'VS Code, Python, Java JDK, Android Studio, Arduino IDE, Git',
                'departments' => json_encode(['DOMIT', 'DECET']),
                'is_active'   => true,
                'sort_order'  => 5,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'name'        => 'Database & Server Tools',
                'short'       => 'DB & Server Tools',
                'icon'        => '🗄️',
                'category'    => 'Databases & Networking',
                'description' => 'MySQL Workbench, XAMPP, Cisco Packet Tracer, Wireshark',
                'departments' => json_encode(['DOMIT', 'DECET']),
                'is_active'   => true,
                'sort_order'  => 6,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ];

        DB::table('softwares')->insert($initialSoftware);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('softwares');
    }
};
