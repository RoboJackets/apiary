<template>
  <div class="d-flex flex-column flex-grow-1 overflow-hidden rj-resume-book" style="min-height:0;">

    <!-- Page heading -->
    <div class="rj-page-header px-4 py-3 flex-shrink-0 d-flex align-items-center justify-content-between border-bottom">
      <h1 class="h4 mb-0 fw-semibold">Resume Book</h1>
      <button class="btn btn-sm btn-outline-secondary">
        Export CSV <span class="badge bg-secondary ms-1">{{ users.length }}</span>
      </button>
    </div>

    <!-- Folder tab bar -->
    <div class="rj-tabs flex-shrink-0 px-3 pt-2 border-bottom d-flex align-items-end gap-1 overflow-x-auto">
      <button class="btn btn-sm rj-tab rj-tab--active">All Students</button>
      <button class="btn btn-sm rj-tab rj-tab--inactive">Robotics Leads <span class="badge bg-secondary ms-1">3</span></button>
      <button class="btn btn-sm rj-tab rj-tab--inactive">Summer Hires <span class="badge bg-secondary ms-1">2</span></button>
      <button class="btn btn-sm rj-tab rj-tab--new ms-1">+ New Folder</button>
    </div>

    <!-- Body -->
    <div class="row flex-grow-1 overflow-hidden g-0" style="min-height:0;">

      <!-- Sidebar -->
      <div class="col-auto border-end overflow-auto h-100 rj-sidebar py-3 px-3" style="min-height:0;">
        <input class="form-control form-control-sm mb-3" placeholder="Search by name…" type="search"/>

        <!-- Major -->
        <div class="rj-collapsible-header" @click="expanded.major = !expanded.major">
          <p class="rj-filter-label mb-0">Major</p>
          <span class="rj-chevron" :class="{ open: expanded.major }">&#9654;</span>
        </div> <!-- TODO: pass in majors to the page -->
        <div v-show="expanded.major" class="mb-3 mt-1">
          <div v-for="m in majors" :key="m" class="form-check mb-1">
            <input class="form-check-input" type="checkbox" :id="'m-'+m"/>
            <label class="form-check-label small" :for="'m-'+m">{{ m }}</label>
          </div>
        </div>

        <!-- Graduation Term -->
        <div class="rj-collapsible-header" @click="expanded.term = !expanded.term">
          <p class="rj-filter-label mb-0">Graduation Term</p>
          <span class="rj-chevron" :class="{ open: expanded.term }">&#9654;</span>
        </div>
        <div v-show="expanded.term" class="mb-3 mt-1">
          <div v-for="t in terms" :key="t" class="form-check mb-1">
            <input class="form-check-input" type="checkbox" :id="'t-'+t"/>
            <label class="form-check-label small" :for="'t-'+t">{{ t }}</label>
          </div>
        </div>

        <!-- Skills & Keywords -->
        <p class="rj-filter-label">Skills &amp; Keywords</p>
        <input class="form-control form-control-sm mb-2" placeholder="Filter keywords…" type="search"/>
        <div v-for="(kws, cat) in keywordCategories" :key="cat" class="mb-2">
          <button class="btn btn-link btn-sm p-0 rj-link d-flex justify-content-between w-100 text-start"
                  @click="toggleCat(cat)">
            <span class="small fw-semibold">{{ cat }}</span>
            <span class="text-muted">{{ expandedCats[cat] ? '▾' : '▸' }}</span>
          </button>
          <div v-if="expandedCats[cat]" class="d-flex flex-wrap gap-1 pt-1 pb-2">
            <span v-for="kw in kws" :key="kw" class="badge rj-filter-badge" style="cursor:pointer;">{{ kw }}</span>
          </div>
        </div>
      </div>

      <!-- Student list -->
      <div class="overflow-auto h-100 border-end"
           :class="selectedUser ? 'col-auto rj-list-narrow' : 'col'"
           style="min-height:0;">
        <div class="px-3 py-2 border-bottom d-flex justify-content-between align-items-center">
          <small class="text-muted">{{ users.length }} students</small>
          <button v-if="selectedUser" class="btn btn-link btn-sm p-0 rj-link" @click="selectedUser = null">← Collapse</button>
        </div>
        <table class="table table-hover table-sm mb-0">
          <thead class="table-light sticky-top">
            <tr>
              <th>Name</th>
              <th v-if="!selectedUser">Major</th>
              <th v-if="!selectedUser">Grad Term</th>
              <th v-if="!selectedUser">Skills</th>
              <th class="rj-action-th"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="user in users" :key="user.id"
                :class="{ 'rj-row--active': selectedUser && selectedUser.id === user.id }"
                style="cursor:pointer;"
                @click="selectUser(user)">
              <td class="align-middle">
                <span class="fw-medium">{{ user.name }}</span>
                <span v-if="user.saved" class="rj-saved-star ms-1">★</span>
              </td>
              <td v-if="!selectedUser" class="align-middle small text-muted">{{ user.majors.map(m => m.name).join(', ') }}</td>
              <td v-if="!selectedUser" class="align-middle small">{{ user.graduation_semester }}</td>
              <td v-if="!selectedUser" class="align-middle">
                <!-- <div class="d-flex flex-wrap gap-1">
                  <span v-for="tag in user.tags.slice(0, 3)" :key="tag" class="badge rj-skill-badge">{{ tag }}</span>
                  <span v-if="user.tags.length > 3" class="text-muted small">+{{ user.tags.length - 3 }}</span>
                </div> --> <!-- TODO: Add tags to user objects -->
              </td>
              <td class="rj-action-cell" @click.stop>
                <div class="rj-dd-wrap">
                  <button class="rj-dot-btn" @click.stop="openDd = openDd === user.id ? null : user.id">•••</button>
                  <div class="rj-dropdown" :class="{ open: openDd === user.id }">
                    <button>Add to group</button>
                    <button>Email</button>
                  </div>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Detail / PDF panel -->
      <div v-if="selectedUser" class="col overflow-auto h-100 rj-detail-panel" style="min-height:0;">
        <div class="px-4 py-3 border-bottom">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <h2 class="h5 mb-0 fw-semibold">{{ selectedUser.full_name }}</h2>
              <div class="text-muted small">{{ selectedUser.majors.map(m => m.name).join(', ') }} · {{ selectedUser.graduation_semester }} · {{ selectedUser.email }}</div>
            </div>
            <button class="btn btn-sm btn-primary">Download</button>
          </div>
          <!-- <div class="d-flex flex-wrap gap-1 mt-2">
            <span v-for="tag in selectedUser.tags" :key="tag" class="badge rj-skill-badge">{{ tag }}</span>
          </div> --> <!-- TODO: Add user tags -->
        </div>
        <div class="rj-pdf-frame d-flex align-items-center justify-content-center text-muted">
          <object v-if="resume_url" :data="resume_url" type="application/pdf" width="100%" height="100%" >
            <small>Your browser does not support PDFs. <a :href="resume_url">Download</a></small>
          </object>
          <small v-else>No resume PDF found.</small>
        </div>
      </div>

    </div>
  </div>
</template>

<script>
export default {
  name: 'ResumeBookIndex',
  props: {
    // Expect an array of user objects to be passed in when the page loads.
    // Each user object can contain: name, uid, major, graduation_semester
    users: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      selectedUser: null,
      openDd: null,
      expanded: { major: true, term: true },
      expandedCats: {},
      // TODO: majors and should be grabbed from database
      majors: ['Computer Engineering', 'Computer Science', 'Electrical Engineering', 'Mechanical Engineering', 'Industrial Engineering'],
      terms:  ['Spring 2025', 'Fall 2025', 'Spring 2026', 'Fall 2026'],
      
      // TODO: Should be a model instead of hardcoded
      keywordCategories: {
        'Programming Languages': ['Python', 'C++', 'Java', 'JavaScript', 'MATLAB', 'SQL'],
        'Robotics & Embedded':   ['ROS', 'ROS2', 'Embedded Systems', 'RTOS', 'Arduino', 'Firmware'],
        'Mechanical & Design':   ['SolidWorks', 'CAD', 'Fusion 360', 'FEA', 'GD&T', '3D Printing'],
        'AI / ML':               ['Machine Learning', 'Computer Vision', 'PyTorch', 'TensorFlow', 'SLAM'],
        'Software & DevOps':     ['Git', 'Linux', 'Docker', 'CI/CD', 'REST API'],
      },
      // TODO: Fetch users
      users: [
        // { id:  1, name: 'Alex Chen',       major: 'Computer Engineering',    term: 'Fall 2025',   email: 'alex.chen@gatech.edu',       saved: true,  tags: ['Python', 'ROS2', 'C++', 'Linux', 'Git'] },
        // { id:  2, name: 'Jordan Lee',       major: 'Mechanical Engineering',  term: 'Fall 2025',   email: 'jordan.lee@gatech.edu',      saved: false, tags: ['SolidWorks', 'Python', 'CAD', 'FEA'] },
        // { id:  3, name: 'Priya Sharma',     major: 'Computer Science',        term: 'Fall 2025',   email: 'priya.sharma@gatech.edu',    saved: true,  tags: ['Machine Learning', 'PyTorch', 'ROS2', 'Python'] },
        // { id:  4, name: 'Marcus Webb',      major: 'Electrical Engineering',  term: 'Spring 2026', email: 'marcus.webb@gatech.edu',     saved: false, tags: ['Embedded Systems', 'C++', 'RTOS', 'FPGA'] },
        // { id:  5, name: 'Sofia Reyes',      major: 'Computer Engineering',    term: 'Spring 2026', email: 'sofia.reyes@gatech.edu',     saved: false, tags: ['ROS', 'Python', 'SLAM', 'Computer Vision'] },
        // { id:  6, name: 'Daniel Park',      major: 'Computer Science',        term: 'Fall 2025',   email: 'daniel.park@gatech.edu',     saved: true,  tags: ['Docker', 'CI/CD', 'Python', 'REST API', 'Linux'] },
        // { id:  7, name: 'Aisha Okafor',     major: 'Mechanical Engineering',  term: 'Spring 2025', email: 'aisha.okafor@gatech.edu',    saved: false, tags: ['SolidWorks', 'MATLAB', 'FEA', '3D Printing'] },
        // { id:  8, name: 'Tyler Nguyen',     major: 'Computer Engineering',    term: 'Fall 2026',   email: 'tyler.nguyen@gatech.edu',    saved: false, tags: ['C++', 'Firmware', 'Arduino', 'Embedded Systems'] },
        // { id:  9, name: 'Rachel Kim',       major: 'Computer Science',        term: 'Spring 2026', email: 'rachel.kim@gatech.edu',      saved: true,  tags: ['TensorFlow', 'Python', 'SQL', 'Machine Learning'] },
        // { id: 10, name: 'Ben Torres',       major: 'Electrical Engineering',  term: 'Fall 2025',   email: 'ben.torres@gatech.edu',      saved: false, tags: ['Circuit Design', 'MATLAB', 'Python', 'Signal Processing'] },
        // { id: 11, name: 'Mia Johnson',      major: 'Industrial Engineering',  term: 'Spring 2026', email: 'mia.johnson@gatech.edu',     saved: false, tags: ['Python', 'SQL', 'Tableau', 'Excel'] },
        // { id: 12, name: 'Ethan Brooks',     major: 'Computer Engineering',    term: 'Fall 2025',   email: 'ethan.brooks@gatech.edu',    saved: false, tags: ['ROS2', 'C++', 'Linux', 'Git', 'Docker'] },
        // { id: 13, name: 'Leila Nasser',     major: 'Mechanical Engineering',  term: 'Fall 2026',   email: 'leila.nasser@gatech.edu',    saved: false, tags: ['CAD', 'Fusion 360', 'GD&T', 'SolidWorks'] },
        // { id: 14, name: 'James Osei',       major: 'Computer Science',        term: 'Spring 2025', email: 'james.osei@gatech.edu',      saved: true,  tags: ['JavaScript', 'REST API', 'Docker', 'Linux'] },
        // { id: 15, name: 'Hannah Cole',      major: 'Electrical Engineering',  term: 'Fall 2025',   email: 'hannah.cole@gatech.edu',     saved: false, tags: ['Embedded Systems', 'RTOS', 'C++', 'Firmware', 'Arduino'] },
        // { id: 16, name: 'Owen Martinez',    major: 'Computer Engineering',    term: 'Spring 2026', email: 'owen.martinez@gatech.edu',   saved: false, tags: ['SLAM', 'ROS2', 'Python', 'Computer Vision'] },
        // { id: 17, name: 'Zara Patel',       major: 'Computer Science',        term: 'Fall 2026',   email: 'zara.patel@gatech.edu',      saved: false, tags: ['Machine Learning', 'Python', 'PyTorch', 'SQL'] },
        // { id: 18, name: 'Noah Griffin',     major: 'Mechanical Engineering',  term: 'Fall 2025',   email: 'noah.griffin@gatech.edu',    saved: false, tags: ['SolidWorks', 'FEA', 'MATLAB', '3D Printing', 'CAD'] },
        // { id: 19, name: 'Camille Dubois',   major: 'Industrial Engineering',  term: 'Spring 2025', email: 'camille.dubois@gatech.edu',  saved: false, tags: ['Python', 'Excel', 'Tableau', 'SQL'] },
        // { id: 20, name: 'Liam Fitzgerald',  major: 'Electrical Engineering',  term: 'Spring 2026', email: 'liam.fitzgerald@gatech.edu', saved: true,  tags: ['FPGA', 'VHDL', 'C++', 'Signal Processing'] },
      ],
      resume_url: '',
    };
  },

  methods: {
    toggleCat(cat) {
      this.$set(this.expandedCats, cat, !this.expandedCats[cat]);
    },
    async selectUser(user) {
      var url = '';
      const uid = user.uid;
      try {
        const response = await fetch(`/sponsor/resumes/${uid}`);
        if (!response.ok) {
            throw new Error(`Failed to load PDF.`)
        }
        if (this.resume_url) {
          URL.revokeObjectURL(this.resume_url);
        }
        const blob = await response.blob();
        url = URL.createObjectURL(blob);
      } catch (error) {
        console.log(`Error retrieving PDF to view: ${error}`);
      } finally {
        this.resume_url = url;
        this.selectedUser = user;
      }
    },
  },
};
</script>