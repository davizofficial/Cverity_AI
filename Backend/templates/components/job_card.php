<?php
// Component: Job Card
$jobs = $jobs ?? [];
?>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex flex-col" style="height: 600px;">
    <div class="flex items-center justify-between mb-4 flex-shrink-0">
        <h3 class="text-xl font-bold text-gray-800">Job Recommendations</h3>
    </div>
    
    <?php if (empty($jobs)): ?>
    <div class="text-center py-8 flex-1 flex flex-col items-center justify-center">
        <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
        </svg>
        <p class="text-gray-600">No job recommendations available.</p>
        <p class="text-sm text-gray-500 mt-2">Try adding more skills to your CV.</p>
    </div>
    <?php else: ?>
    
    <!-- Search Bar -->
    <div class="mb-4 flex-shrink-0">
        <div class="relative">
            <input type="text" id="job-search" placeholder="Search jobs..." 
                   class="w-full px-4 py-2 pl-10 border border-gray-300 rounded-lg focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary focus:ring-opacity-20 text-sm">
            <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="mb-4 flex-shrink-0">
        <div class="grid grid-cols-2 gap-2">
            <select id="filter-experience" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-primary">
                <option value="">Experience</option>
                <option value="internship">Internship</option>
                <option value="entry">Entry Level</option>
                <option value="mid">Mid Level</option>
                <option value="senior">Senior</option>
            </select>
            <select id="filter-location" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-primary">
                <option value="">Location</option>
                <option value="remote">Remote</option>
                <option value="jakarta">Jakarta</option>
                <option value="bandung">Bandung</option>
                <option value="surabaya">Surabaya</option>
            </select>
        </div>
        <button onclick="applyJobFilters()" class="w-full mt-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition text-sm font-medium">
            <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
            </svg>
            Filter
        </button>
    </div>
    
    <div class="space-y-3 overflow-y-auto pr-2 flex-1" style="scrollbar-width: thin; scrollbar-color: #cbd5e0 #f7fafc;" id="job-list">
        <?php foreach ($jobs as $index => $job): ?>
        <div class="job-item bg-white border border-gray-200 rounded-lg p-4 hover:shadow-lg hover:border-primary transition-all duration-200" 
             data-title="<?= htmlspecialchars(strtolower($job['title'])) ?>"
             data-company="<?= htmlspecialchars(strtolower($job['company'])) ?>"
             data-location="<?= htmlspecialchars(strtolower($job['location'])) ?>">
            <!-- Header -->
            <div class="flex items-start justify-between mb-3">
                <div class="flex-1 min-w-0">
                    <h4 class="font-bold text-gray-900 text-base mb-1 truncate"><?= htmlspecialchars($job['title']) ?></h4>
                    <div class="flex items-center gap-2 text-sm text-gray-600 mb-1">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        <span class="font-medium truncate"><?= htmlspecialchars($job['company']) ?></span>
                    </div>
                    <div class="flex items-center gap-2 text-xs text-gray-500">
                        <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span class="truncate"><?= htmlspecialchars($job['location']) ?></span>
                    </div>
                </div>
                <div class="ml-3 flex flex-col items-end gap-1 flex-shrink-0">
                    <?php 
                    $score = $job['match_score'];
                    $bgColor = $score >= 85 ? 'bg-green-100 text-green-800' : ($score >= 70 ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800');
                    ?>
                    <div class="px-2 py-0.5 <?= $bgColor ?> rounded-full text-xs font-bold whitespace-nowrap">
                        <?= $score ?>% Match
                    </div>
                </div>
            </div>
            
            <!-- Description -->
            <p class="text-xs text-gray-600 mb-3 line-clamp-2"><?= htmlspecialchars($job['description']) ?></p>
            
            <!-- Footer -->
            <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                <?php if (!empty($job['posted'])): ?>
                <span class="text-xs text-gray-500">
                    Posted <?php
                    $posted = strtotime($job['posted']);
                    $diff = time() - $posted;
                    $days = floor($diff / 86400);
                    echo $days == 0 ? 'today' : ($days == 1 ? 'yesterday' : $days . 'd ago');
                    ?>
                </span>
                <?php else: ?>
                <span class="text-xs text-gray-400">New</span>
                <?php endif; ?>
                <a href="<?= htmlspecialchars($job['url']) ?>" target="_blank" 
                   class="inline-flex items-center gap-1 px-3 py-1.5 bg-primary text-white rounded-lg hover:bg-primary-dark transition text-xs font-medium">
                    Apply
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <?php endif; ?>
</div>

<script>
// Job search and filter functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('job-search');
    const filterExperience = document.getElementById('filter-experience');
    const filterLocation = document.getElementById('filter-location');
    
    if (searchInput) {
        searchInput.addEventListener('input', filterJobs);
    }
});

function filterJobs() {
    const searchTerm = document.getElementById('job-search')?.value.toLowerCase() || '';
    const jobItems = document.querySelectorAll('.job-item');
    
    jobItems.forEach(item => {
        const title = item.dataset.title || '';
        const company = item.dataset.company || '';
        const location = item.dataset.location || '';
        
        const matches = title.includes(searchTerm) || 
                       company.includes(searchTerm) || 
                       location.includes(searchTerm);
        
        item.style.display = matches ? 'block' : 'none';
    });
}

function applyJobFilters() {
    const experience = document.getElementById('filter-experience')?.value.toLowerCase() || '';
    const location = document.getElementById('filter-location')?.value.toLowerCase() || '';
    const searchTerm = document.getElementById('job-search')?.value.toLowerCase() || '';
    const jobItems = document.querySelectorAll('.job-item');
    
    jobItems.forEach(item => {
        const itemTitle = item.dataset.title || '';
        const itemCompany = item.dataset.company || '';
        const itemLocation = item.dataset.location || '';
        
        let matches = true;
        
        // Search filter
        if (searchTerm) {
            matches = matches && (itemTitle.includes(searchTerm) || 
                                 itemCompany.includes(searchTerm) || 
                                 itemLocation.includes(searchTerm));
        }
        
        // Experience filter
        if (experience) {
            matches = matches && itemTitle.includes(experience);
        }
        
        // Location filter
        if (location) {
            matches = matches && itemLocation.includes(location);
        }
        
        item.style.display = matches ? 'block' : 'none';
    });
}
</script>

<style>
/* Custom scrollbar untuk job recommendations */
#job-list::-webkit-scrollbar {
    width: 8px;
}

#job-list::-webkit-scrollbar-track {
    background: #f7fafc;
    border-radius: 4px;
}

#job-list::-webkit-scrollbar-thumb {
    background: #cbd5e0;
    border-radius: 4px;
}

#job-list::-webkit-scrollbar-thumb:hover {
    background: #a0aec0;
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
