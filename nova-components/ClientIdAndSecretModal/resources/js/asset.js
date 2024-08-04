import ClientIdAndSecretModal from './components/ClientIdAndSecretModal'

Nova.booting(app => {
  app.component('client-id-and-secret-modal', ClientIdAndSecretModal)
})
