import PersonalAccessTokenModal from './components/PersonalAccessTokenModal'

Nova.booting(app => {
  app.component('personal-access-token-modal', PersonalAccessTokenModal)
});
