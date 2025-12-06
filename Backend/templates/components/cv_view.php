<?php
// Component: CV Viewer
$cvData = $cvData ?? [];
?>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <h3 class="text-xl font-bold text-gray-800 mb-4">Data CV Terstruktur</h3>
    
    <!-- Personal Info -->
    <div class="mb-6">
        <h4 class="font-semibold text-gray-700 mb-2">Informasi Pribadi</h4>
        <p class="text-lg font-bold text-gray-900"><?= htmlspecialchars($cvData['name'] ?? 'N/A') ?></p>
        <p class="text-gray-600"><?= htmlspecialchars(implode(', ', $cvData['emails'] ?? [])) ?></p>
        <p class="text-gray-600"><?= htmlspecialchars(implode(', ', $cvData['phones'] ?? [])) ?></p>
    </div>
    
    <!-- Summary -->
    <?php if (!empty($cvData['summary'])): ?>
    <div class="mb-6">
        <h4 class="font-semibold text-gray-700 mb-2">Ringkasan</h4>
        <p class="text-gray-600"><?= htmlspecialchars($cvData['summary']) ?></p>
    </div>
    <?php endif; ?>
    
    <!-- Experience -->
    <div class="mb-6">
        <h4 class="font-semibold text-gray-700 mb-3">Pengalaman Kerja (<?= $cvData['total_experience_years'] ?? 0 ?> tahun)</h4>
        <?php foreach ($cvData['positions'] ?? [] as $pos): ?>
        <div class="mb-4 pb-4 border-b border-gray-100 last:border-0">
            <p class="font-semibold text-gray-800"><?= htmlspecialchars($pos['title']) ?></p>
            <p class="text-gray-600"><?= htmlspecialchars($pos['company']) ?></p>
            <p class="text-sm text-gray-500"><?= htmlspecialchars($pos['start_date']) ?> - <?= htmlspecialchars($pos['end_date']) ?> (<?= $pos['months'] ?> bulan)</p>
            <?php if (!empty($pos['description'])): ?>
            <p class="text-sm text-gray-600 mt-2"><?= htmlspecialchars($pos['description']) ?></p>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Skills -->
    <div class="mb-6">
        <h4 class="font-semibold text-gray-700 mb-2">Skills</h4>
        <div class="flex flex-wrap gap-2">
            <?php foreach ($cvData['skills'] ?? [] as $skill): ?>
            <span class="px-3 py-1 bg-primary-light bg-opacity-20 text-primary rounded-full text-sm"><?= htmlspecialchars($skill) ?></span>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Education -->
    <div>
        <h4 class="font-semibold text-gray-700 mb-2">Pendidikan</h4>
        <?php foreach ($cvData['education'] ?? [] as $edu): ?>
        <div class="mb-2">
            <p class="font-semibold text-gray-800"><?= htmlspecialchars($edu['degree']) ?></p>
            <p class="text-gray-600"><?= htmlspecialchars($edu['institution']) ?> <?= $edu['year'] ? '(' . $edu['year'] . ')' : '' ?></p>
        </div>
        <?php endforeach; ?>
    </div>
</div>
