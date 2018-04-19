export default notGTEmail => {
  return !notGTEmail.trim().endsWith('gatech.edu');
};