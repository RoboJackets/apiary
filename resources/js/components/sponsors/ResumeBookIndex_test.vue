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
          <div v-for="m in majors" :key="m.id" class="form-check mb-1">
            <input class="form-check-input" type="checkbox" @click="() => {
              toggleMajor(m);
            }" :id="'m-'+m.id"/>
            <label class="form-check-label small" :for="'m-'+m.id">{{ m.display_name ?? m.gtad_majorgroup_name }}</label>
          </div>
        </div>

        <!-- Graduation Term -->
        <div class="rj-collapsible-header" @click="expanded.term = !expanded.term">
          <p class="rj-filter-label mb-0">Graduation Term</p>
          <span class="rj-chevron" :class="{ open: expanded.term }">&#9654;</span>
        </div>
        <div v-show="expanded.term" class="mb-3 mt-1">
          <div v-for="t in terms" :key="t.code" class="form-check mb-1">
            <input class="form-check-input" type="checkbox" @click="(t) => {
              toggleGraduationSemester(t);
            }" :id="'t-'+t.code"/>
            <label class="form-check-label small" :for="'t-'+t.code">{{ t.name }}</label>
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
              <td v-if="!selectedUser" class="align-middle small">{{ user.graduation_semester.name }}</td>
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
              <div class="text-muted small">{{ selectedUser.majors.map(m => m.name).join(', ') }} · {{ selectedUser.graduation_semester.name }} · {{ selectedUser.email }}</div>
            </div>
            <button class="btn btn-sm btn-primary">Download</button>
          </div>
          <!-- <div class="d-flex flex-wrap gap-1 mt-2">
            <span v-for="tag in selectedUser.tags" :key="tag" class="badge rj-skill-badge">{{ tag }}</span>
          </div> --> <!-- TODO: Add user tags -->
        </div>
        <div class="rj-pdf-frame d-flex align-items-center justify-content-center text-muted">
          <iframe v-if="resume_url" :src="resume_url" type="application/pdf" class="w-100 h-100 border-0" >
            <!-- <small>Your browser does not support PDFs. <a :href="resume_url">Download</a></small> -->
          </iframe>
          <small v-else>No resume PDF found.</small>
        </div>
      </div>

    </div>
  </div>
</template>

<script>
export default {
  name: 'ResumeBookIndex',
  data() {
    return {
      users: [],
      filters: {
        majors: [],
        graduation_semesters: [],
        majors: [],
      },
      selectedUser: null,
      openDd: null,
      expanded: { major: true, term: true },
      expandedCats: {},
      majors: [],
      terms:  [],
      
      // TODO: Should be a model instead of hardcoded
      keywordCategories: {
        'Programming Languages': ['Python', 'C++', 'Java', 'JavaScript', 'MATLAB', 'SQL'],
        'Robotics & Embedded':   ['ROS', 'ROS2', 'Embedded Systems', 'RTOS', 'Arduino', 'Firmware'],
        'Mechanical & Design':   ['SolidWorks', 'CAD', 'Fusion 360', 'FEA', 'GD&T', '3D Printing'],
        'AI / ML':               ['Machine Learning', 'Computer Vision', 'PyTorch', 'TensorFlow', 'SLAM'],
        'Software & DevOps':     ['Git', 'Linux', 'Docker', 'CI/CD', 'REST API'],
      },
      resume_url: '',
    };
  },

  // TODO: produce toast or something when error occurs
  async mounted() {
    await this.search();
    await this.getGraduationSemesters();
    await this.getMajors();
  },

  watch: {
    filters: {
      handler() {
        this.search();
      },
      deep: true,
    },
  },

  methods: {
    toggleCat(cat) {
      this.$set(this.expandedCats, cat, !this.expandedCats[cat]);
    },
    async selectUser(user) {
      if (this.resume_url) {
        URL.revokeObjectURL(this.resume_url);
      }
      this.resume_url = `/sponsor/resumes/${user.uid}`
      this.selectedUser = user;
    },

    async search() {
      try {
        const response = await axios.post('/sponsor/search', this.filters);
        this.users = response.data.users;
      } catch (error) {
        console.error('Error fetching users:', error);
      }
    },

    async getGraduationSemesters() {
      try {
        const response = await axios.get('/sponsor/graduation-semesters');
        this.terms = response.data.graduation_semesters;
      } catch (error) {
        console.error('Error fetching graduation semesters:', error);
      }
    },

    async getMajors() {
      try {
        const response = await axios.get('/sponsor/majors');
        this.majors = response.data.majors;
      } catch (error) {
        console.error('Error fetching majors:', error);
      }
    },

    toggleMajor(major) {
      const index = this.filters.majors.findIndex(m => m.id === major.id);
      if (index === -1) {
        this.filters.majors.push(major.id);
      } else {
        this.filters.majors.splice(index, 1);
      }
    },

    toggleGraduationSemester(semester) {
      console.log(semester);
      const index = this.filters.graduation_semesters.findIndex(s => s.code === semester.code);
      console.log(document.getElementById('t-'+semester.code));
      if (index === -1 && document.getElementById('t-'+semester.code).checked) {
        this.filters.graduation_semesters.push(semester.code);
      } else {
        this.filters.graduation_semesters.splice(index, 1);
      }
    },
  },
};
</script>