var midas = midas || {};
midas.challenge = midas.challenge || {};
midas.challenge.admin = midas.challenge.admin || {};

midas.challenge.admin.createChallenge = function (id) {
    midas.loadDialog('createChallenge','/challenge/admin/create?communityId=' + id);
    midas.showDialog('Create a Challenge',false);
};