import React from 'react';
import PropTypes from 'prop-types';
import { mapProfileContactsProps, mapProfileContactsDispatch } from '../maps/Profile/ProfileContacts.map';
import { connect } from 'react-redux';
import { DuxOkDialog, DuxYesNoDialog } from 'duxpanel';
import { EditEmail } from './EditEmail.jsx';
import { EditPhone } from './EditPhone.jsx';
import { formatPhoneNumber } from '../../util/util';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import faComment from '@fortawesome/fontawesome-free-solid/faComment';
import faEdit from '@fortawesome/fontawesome-free-solid/faEdit';
import faTrash from '@fortawesome/fontawesome-free-solid/faTrash';
import faPlus from '@fortawesome/fontawesome-free-solid/faPlus';

class ProfileContactsUi extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            ShowTestSentDialog: false
        };
    }

    componentDidMount() {
        this.props.init();
    }

    render() {
        const contacts = this.props.Contacts.map(contact => {
            let display = contact.Contact;
            if (display.length === 10 && display.search(/[^0-9]/) === -1) {
                display = formatPhoneNumber(contact.Contact);
            }
            return (
                <li key={contact.ContactId} style={{cursor:'pointer'}} onClick={() => this.props.contactSelected(contact.ContactId)} className={'list-group-item' + (contact.ContactId === this.props.SelectedContactId ? ' list-group-item-dark' : '')}>
                    {display}
                    { contact.ContactId === this.props.SelectedContactId &&
                        <div className="mt-2">
                            <button type="button" className="btn btn-sm btn-secondary" onClick={this.props.editClicked}><FontAwesomeIcon icon={faEdit}/> Edit</button>
                            <button type="button" className="btn btn-sm btn-secondary ml-2" onClick={this.props.testClicked}><FontAwesomeIcon icon={faComment}/> Test</button>
                            <button type="button" className="btn btn-sm btn-danger ml-2" onClick={this.props.deleteClicked}><FontAwesomeIcon icon={faTrash}/> Delete</button>
                        </div>
                    }
                </li>
            );
        });
        return (
            <div>
                <h4>Contact Information</h4>
                { this.props.Contacts.length > 0 &&
                <small className="text-info">Click or tap an item for options.</small>
                }
                <ul className="list-group mt-2">
                    {contacts}
                </ul>
                <div className="mt-2 text-center">
                    <button type="button" className="btn btn-secondary btn-sm" onClick={this.props.newPhoneClicked}><FontAwesomeIcon icon={faPlus}/> Mobile</button>
                    <button type="button" className="btn btn-secondary btn-sm ml-2" onClick={this.props.newEmailClicked}><FontAwesomeIcon icon={faPlus}/> Email</button>
                </div>
                <small className="text-muted"><em>Add your contact information only. Use separate accounts for other family members.</em></small>
                <DuxYesNoDialog show={this.props.ShowDeleteDialog}
                                title="Delete Contact Record"
                                yesClassName="btn btn-danger"
                                noClassName="btn btn-primary"
                                onNo={this.props.deleteNoClicked}
                                onYes={this.props.deleteYesClicked}
                                width={{xs:'90%',sm:400}}
                >
                    Are you sure you want to delete the contact record?
                </DuxYesNoDialog>
                <DuxOkDialog show={this.state.ShowTestSentDialog}
                             title="Test Message Sent"
                             okClassName="btn btn-primary"
                             onOk={() => this.setState({ShowTestSentDialog:false})}
                             allowEnter={false}
                             allowClose={false}
                             width={{xs:'90%',sm:400}}
                >
                    A test message has been sent.
                </DuxOkDialog>
                <EditEmail show={this.props.ShowEmailDialog}
                           email={this.props.EmailToEdit}
                           cancelClicked={this.props.emailCancelClicked}
                           okClicked={this.props.emailOkClicked}
                />
                <EditPhone show={this.props.ShowPhoneDialog}
                           phone={this.props.PhoneToEdit}
                           cancelClicked={this.props.phoneCancelClicked}
                           okClicked={this.props.phoneOkClicked}
                />
            </div>
        );
    }
}

ProfileContactsUi.propTypes = {
    Contacts: PropTypes.array.isRequired,
    EmailToEdit: PropTypes.string.isRequired,
    PhoneToEdit: PropTypes.string.isRequired,
    SelectedContactId: PropTypes.number.isRequired,
    ShowDeleteDialog: PropTypes.bool.isRequired,
    ShowEmailDialog: PropTypes.bool.isRequired,
    ShowPhoneDialog: PropTypes.bool.isRequired,

    init: PropTypes.func.isRequired,
    contactSelected: PropTypes.func.isRequired,
    deleteClicked: PropTypes.func.isRequired,
    deleteNoClicked: PropTypes.func.isRequired,
    deleteYesClicked: PropTypes.func.isRequired,
    editClicked: PropTypes.func.isRequired,
    emailCancelClicked: PropTypes.func.isRequired,
    emailOkClicked: PropTypes.func.isRequired,
    newEmailClicked: PropTypes.func.isRequired,
    newPhoneClicked: PropTypes.func.isRequired,
    phoneCancelClicked: PropTypes.func.isRequired,
    phoneOkClicked: PropTypes.func.isRequired,
    testClicked: PropTypes.func.isRequired
};

export const ProfileContacts = connect(mapProfileContactsProps, mapProfileContactsDispatch)(ProfileContactsUi);
