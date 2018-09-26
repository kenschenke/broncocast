import $ from 'jquery';
import C from '../constants';
import { getFormFieldValue } from 'duxform';

export const deleteContact = () => (dispatch, getState) => {
    const state = getState();
    $.ajax(`/api/contacts/${state.profile_contacts.selectedContactId}`, {
        method: 'DELETE',
        success: data => {
            if (!data.Success) {
                alert(data.Error);
                return;
            }

            dispatch({
                type: C.SET_PROFILE_CONTACTS_DATA,
                payload: {
                    contacts: state.profile_contacts.contacts.filter(contact => contact.ContactId !== state.profile_contacts.selectedContactId),
                    showDeleteDialog: false,
                    selectedContactId: 0
                }
            });
        }
    });
};

export const editContact = () => (dispatch, getState) => {
    const state = getState();
    const selected = state.profile_contacts.contacts.filter(contact => contact.ContactId === state.profile_contacts.selectedContactId);
    if (selected.length !== 1) {
        return;
    }

    if (selected[0].Contact.search(/[^0-9]/) !== -1) {
        dispatch({
            type: C.SET_PROFILE_CONTACTS_DATA,
            payload: {
                showEmailDialog: true,
                emailToEdit: selected[0].Contact,
                editingContactId: state.profile_contacts.selectedContactId
            }
        });
    } else {
        dispatch({
            type: C.SET_PROFILE_CONTACTS_DATA,
            payload: {
                showPhoneDialog: true,
                phoneToEdit: selected[0].Contact,
                editingContactId: state.profile_contacts.selectedContactId
            }
        });
    }
};

export const getContacts = () => dispatch => {
    $.get('/api/contacts', data => {
        if (!data.Success) {
            alert(data.Error);
            return;
        }

        dispatch({
            type: C.SET_PROFILE_CONTACTS_DATA,
            payload: {
                contacts: data.Contacts
            }
        });
    });
};

export const saveEmail = () => (dispatch, getState) => {
    const state = getState();
    const email = getFormFieldValue(state, 'editemail', 'email', '');

    if (state.profile_contacts.editingContactId) {
        $.ajax(`/api/contacts/${state.profile_contacts.editingContactId}`, {
            contentType: 'application/x-www-form-urlencoded',
            method: 'PUT',
            data: {
                Key: email
            },
            success: data => {
                if (!data.Success) {
                    alert(data.Error);
                    return;
                }

                dispatch({
                    type: C.SET_PROFILE_CONTACTS_DATA,
                    payload: {
                        showEmailDialog: false,
                        contacts: state.profile_contacts.contacts.map(contact => {
                            if (contact.ContactId !== state.profile_contacts.selectedContactId) {
                                return contact;
                            }

                            return {
                                ...contact,
                                Contact: email
                            };
                        })
                    }
                });
            }
        });
    } else {
        $.ajax('/api/contacts', {
            contentType: 'application/x-www-form-urlencoded',
            method: 'POST',
            data: {
                Key: email
            },
            success: data => {
                if (!data.Success) {
                    alert(data.Error);
                    return;
                }

                dispatch({
                    type: C.SET_PROFILE_CONTACTS_DATA,
                    payload: {
                        showEmailDialog: false,
                        contacts: [
                            ...state.profile_contacts.contacts,
                            {
                                ContactId: data.ContactId,
                                Contact: email
                            }
                        ]
                    }
                });
            }
        });
    }
};

export const savePhone = () => (dispatch, getState) => {
    const state = getState();
    const phone = getFormFieldValue(state, 'editphone', 'phone', '');

    if (state.profile_contacts.editingContactId) {
        $.ajax(`/api/contacts/${state.profile_contacts.editingContactId}`, {
            contentType: 'application/x-www-form-urlencoded',
            method: 'PUT',
            data: {
                Key: phone
            },
            success: data => {
                if (!data.Success) {
                    alert(data.Error);
                    return;
                }

                dispatch({
                    type: C.SET_PROFILE_CONTACTS_DATA,
                    payload: {
                        showPhoneDialog: false,
                        contacts: state.profile_contacts.contacts.map(contact => {
                            if (contact.ContactId !== state.profile_contacts.selectedContactId) {
                                return contact;
                            }

                            return {
                                ...contact,
                                Contact: phone
                            };
                        })
                    }
                });
            }
        });
    } else {
        $.ajax('/api/contacts', {
            contentType: 'application/x-www-form-urlencoded',
            method: 'POST',
            data: {
                Key: phone
            },
            success: data => {
                if (!data.Success) {
                    alert(data.Error);
                    return;
                }

                dispatch({
                    type: C.SET_PROFILE_CONTACTS_DATA,
                    payload: {
                        showPhoneDialog: false,
                        contacts: [
                            ...state.profile_contacts.contacts,
                            {
                                ContactId: data.ContactId,
                                Contact: phone
                            }
                        ]
                    }
                });
            }
        });
    }
};

export const testContact = () => (dispatch, getState) => {
    const state = getState();
    $.ajax(`/api/contacts/test/${state.profile_contacts.selectedContactId}`, {
        method: 'PUT',
        success: data => {
            if (!data.Success) {
                alert(data.Error);
                // return;
            }

            // dispatch({
            //     type: C.SET_PROFILE_CONTACTS_DATA,
            //     payload: {
            //         contacts: state.profile_contacts.contacts.filter(contact => contact.ContactId !== state.profile_contacts.selectedContactId),
            //         showDeleteDialog: false,
            //         selectedContactId: 0
            //     }
            // });
        }
    });
};
