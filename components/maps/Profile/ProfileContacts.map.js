import { deleteContact, editContact, getContacts, saveEmail, savePhone, testContact } from '../../../actions/profile_contacts';
import C from '../../../constants';

export const mapProfileContactsProps = state => {
    return {
        Contacts: state.profile_contacts.contacts,
        EmailToEdit: state.profile_contacts.emailToEdit,
        PhoneToEdit: state.profile_contacts.phoneToEdit,
        SelectedContactId: state.profile_contacts.selectedContactId,
        ShowDeleteDialog: state.profile_contacts.showDeleteDialog,
        ShowEmailDialog: state.profile_contacts.showEmailDialog,
        ShowPhoneDialog: state.profile_contacts.showPhoneDialog
    };
};

export const mapProfileContactsDispatch = dispatch => {
    return {
        contactSelected(contactId) {
            dispatch({
                type: C.SET_PROFILE_CONTACTS_DATA,
                payload: {
                    selectedContactId: contactId
                }
            });
        },

        deleteClicked() {
            dispatch({
                type: C.SET_PROFILE_CONTACTS_DATA,
                payload: {
                    showDeleteDialog: true
                }
            });
        },

        deleteNoClicked() {
            dispatch({
                type: C.SET_PROFILE_CONTACTS_DATA,
                payload: {
                    showDeleteDialog: false
                }
            });
        },

        deleteYesClicked() {
            dispatch(deleteContact());
        },

        editClicked() {
            dispatch(editContact());
        },

        emailCancelClicked() {
            dispatch({
                type: C.SET_PROFILE_CONTACTS_DATA,
                payload: {
                    showEmailDialog: false
                }
            });
        },

        emailOkClicked() {
            dispatch(saveEmail());
        },

        init() {
            dispatch({
                type: C.SET_PROFILE_CONTACTS_DATA,
                payload: {
                    emailToEdit: '',
                    phoneToEdit: '',
                    selectedContactId: 0,
                    showDeleteDialog: false,
                    showEmailDialog: false,
                    showPhoneDialog: false
                }
            });
            dispatch(getContacts());
        },

        newEmailClicked() {
            dispatch({
                type: C.SET_PROFILE_CONTACTS_DATA,
                payload: {
                    showEmailDialog: true,
                    emailToEdit: '',
                    editingContactId: 0
                }
            });
        },

        newPhoneClicked() {
            dispatch({
                type: C.SET_PROFILE_CONTACTS_DATA,
                payload: {
                    showPhoneDialog: true,
                    phoneToEdit: '',
                    editingContactId: 0
                }
            });
        },

        phoneCancelClicked() {
            dispatch({
                type: C.SET_PROFILE_CONTACTS_DATA,
                payload: {
                    showPhoneDialog: false
                }
            });
        },

        phoneOkClicked() {
            dispatch(savePhone());
        },

        testClicked() {
            dispatch(testContact());
        }
    };
};
