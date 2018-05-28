export default notGTEmail => {
  return !notGTEmail
    .trim()
    .toLowerCase()
    .endsWith('gatech.edu');
};
