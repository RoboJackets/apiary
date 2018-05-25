export default notGTEmail => {
  if (!notGTEmail) {
    return true;
  }
  return !notGTEmail
    .trim()
    .toLowerCase()
    .endsWith('gatech.edu');
};
