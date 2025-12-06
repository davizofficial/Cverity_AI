<?php
// Component: Gap List
$gaps = $gaps ?? [];
$suggestedActions = $suggestedActions ?? [];
?>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <h3 class="text-xl font-bold text-gray-800 mb-4">Gap Analysis & Saran Perbaikan</h3>
    
    <?php if (empty($gaps)): ?>
    <p class="text-gray-600">Tidak ada gap yang terdeteksi. CV Anda sudah cukup baik!</p>
    <?php else: ?>
    
    <div class="space-y-4 mb-6">
        <?php foreach ($gaps as $gap): ?>
        <div class="border border-gray-200 rounded-lg p-4">
            <div class="flex items-start justify-between mb-2">
                <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-semibold uppercase">
                    <?= htmlspecialchars($gap['type']) ?>
                </span>
            </div>
            <p class="text-gray-700 font-medium mb-2"><?= htmlspecialchars($gap['detail']) ?></p>
            <div class="bg-blue-50 border-l-4 border-primary p-3 rounded">
                <p class="text-sm text-gray-700"><strong>Saran:</strong> <?= htmlspecialchars($gap['suggestion']) ?></p>
            </div>
            <button onclick="copyToClipboard('<?= htmlspecialchars(addslashes($gap['suggestion'])) ?>')" 
                class="mt-2 text-sm text-primary hover:text-primary-dark transition">
                📋 Salin Saran
            </button>
        </div>
        <?php endforeach; ?>
    </div>
    
    <?php endif; ?>
    
    <?php if (!empty($suggestedActions)): ?>
    <div class="pt-6 border-t border-gray-200">
        <h4 class="font-semibold text-gray-700 mb-3">Aksi yang Disarankan:</h4>
        <ol class="list-decimal list-inside space-y-2">
            <?php foreach ($suggestedActions as $action): ?>
            <li class="text-gray-600"><?= htmlspecialchars($action) ?></li>
            <?php endforeach; ?>
        </ol>
    </div>
    <?php endif; ?>
    

</div>
