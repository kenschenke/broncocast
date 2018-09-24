import C from '../../../constants';
import { deleteOrg, editOrg, getOrgs, getTimezones } from '../../../actions/system_orgs';

export const mapSystemOrgsProps = state => {
    return {
        orgs: state.system_orgs.orgs,
        selectedOrgId: state.system_orgs.selectedOrgId,
        showDeleteDialog: state.system_orgs.showDeleteDialog
    };
};

export const mapSystemOrgsDispatch = dispatch => {
    return {
        deleteOrgClicked() {
            dispatch({
                type: C.SET_SYSTEM_ORGS_DATA,
                payload: {
                    showDeleteDialog: true
                }
            });
        },

        deleteNoClicked() {
            dispatch({
                type: C.SET_SYSTEM_ORGS_DATA,
                payload: {
                    showDeleteDialog: false
                }
            });
        },

        deleteYesClicked() {
            dispatch(deleteOrg());
        },

        editOrgClicked() {
            dispatch(editOrg());
        },

        init() {
            dispatch({
                type: C.SET_ROUTER_DATA,
                payload: {
                    route: 'system_orgs',
                    name: 'System Organization Admin'
                }
            });

            dispatch(getOrgs());
            dispatch(getTimezones());
        },

        newOrgClicked() {
            dispatch({
                type: C.SET_SYSTEM_ORGS_DATA,
                payload: {
                    editingDefaultTZ: 'America/Chicago',
                    editingOrgId: 0,
                    editingOrgName: '',
                    showEditDialog: true,
                    editingTag: ''
                }
            });
        },

        orgSelected(OrgId) {
            dispatch({
                type: C.SET_SYSTEM_ORGS_DATA,
                payload: {
                    selectedOrgId: OrgId
                }
            });
        }
    };
};
