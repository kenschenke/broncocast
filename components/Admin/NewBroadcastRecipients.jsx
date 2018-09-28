import React from 'react';
import PropTypes from 'prop-types';
import { mapNewBroadcastRecipientsProps, mapNewBroadcastRecipientsDispatch } from '../maps/Admin/NewBroadcastRecipients.map';
import { connect } from 'react-redux';
import _ from 'lodash';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import faSquare from '@fortawesome/fontawesome-free-regular/faSquare';
import faCheckSquare from '@fortawesome/fontawesome-free-regular/faCheckSquare';
import faMinusSquare from '@fortawesome/fontawesome-free-regular/faMinusSquare';
import faCaretDown from '@fortawesome/fontawesome-free-solid/faCaretDown';
import faCaretRight from '@fortawesome/fontawesome-free-solid/faCaretRight';

class NewBroadcastRecipientsUi extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            expanded: {}
        };
    }

    everyoneClicked = () => {
        if (this.getEveryoneState() === 'full') {
            this.props.unselectEveryone();
        } else {
            this.props.selectEveryone();
        }
    };

    componentDidMount() {
        this.props.init();
    }

    /**
     * Gets the selection state of the group of members
     * @param members
     * @return 'full', 'partial', or 'none'
     */
    getGroupState = members => {
        let selected = 0;
        for (let i = 0; i < members.length; i++) {
            if (this.props.selected.indexOf(members[i].UserId) !== -1) {
                selected++;
            }
        }

        if (selected === 0) {
            return 'none';
        } else {
            return selected === members.length ? 'full' : 'partial';
        }
    };

    getEveryoneState = () => {
        let everyone = '';
        for (let group in this.props.groups) {
            if (this.props.groups.hasOwnProperty(group)) {
                const thisGroup = this.getGroupState(this.props.groups[group]);
                if (everyone === '') {
                    everyone = thisGroup;
                } else if (everyone === 'none') {
                    if (thisGroup !== 'none') {
                        everyone = 'partial';
                    }
                } else if (everyone === 'full') {
                    if (thisGroup !== 'full') {
                        everyone = 'partial';
                    }
                } else {
                    everyone = 'partial';
                }
            }
        }

        return everyone;
    };

    groupCaretClicked = group => {
        const isExpanded = this.isGroupExpanded(group);
        let newExpanded = _.clone(this.state.expanded);
        newExpanded[group] = !isExpanded;
        this.setState({expanded: newExpanded});
    };

    groupSelectClicked = (members, groupState) => {
        const userIds = members.map(member => member.UserId);
        if (groupState === 'full') {
            this.props.unselectAllGroupMembers(userIds);
        } else {
            this.props.selectAllGroupMembers(userIds);
        }
    };

    isGroupExpanded = group => {
        if (!this.state.expanded.hasOwnProperty(group)) {
            return false;
        }

        return this.state.expanded[group];
    };

    render() {
        let everyoneIcon;
        switch (this.getEveryoneState()) {
            case 'none': everyoneIcon = <FontAwesomeIcon icon={faSquare}/>; break;
            case 'partial': everyoneIcon = <FontAwesomeIcon icon={faMinusSquare}/>; break;
            case 'full': everyoneIcon = <FontAwesomeIcon icon={faCheckSquare}/>; break;
        }

        let groups = [];
        for (let group in this.props.groups) {
            if (this.props.groups.hasOwnProperty(group)) {
                let groupIcon;
                const groupState = this.getGroupState(this.props.groups[group]);
                switch (groupState) {
                    case 'none': groupIcon = <FontAwesomeIcon icon={faSquare}/>; break;
                    case 'partial': groupIcon = <FontAwesomeIcon icon={faMinusSquare}/>; break;
                    case 'full': groupIcon = <FontAwesomeIcon icon={faCheckSquare}/>; break;
                }

                const members = this.props.groups[group].map(member => {
                    const memberIcon = this.props.selected.indexOf(member.UserId) === -1 ? <FontAwesomeIcon icon={faSquare}/> : <FontAwesomeIcon icon={faCheckSquare}/>;
                    return <div className="ml-4" key={member.UserId}>
                        <span style={{cursor:'pointer'}} onClick={() => this.props.userClicked(member.UserId)}>{memberIcon}</span> {member.UserName}
                        </div>
                });

                const isExpanded = this.isGroupExpanded(group);
                groups.push(
                    <div key={group} className="ml-2">
                        <div className="newbroadcast-groupcaret" onClick={() => this.groupCaretClicked(group)}><FontAwesomeIcon icon={isExpanded ? faCaretDown : faCaretRight}/></div>
                        <div className="newbroadcast-groupbox" onClick={() => this.groupSelectClicked(this.props.groups[group], groupState)}>{groupIcon}</div>
                        {group}
                        {isExpanded && members}
                    </div>
                );
            }
        }

        return (
            <div>
                <div className="newbroadcast-recipients">
                    <div><span style={{cursor:'pointer'}} onClick={this.everyoneClicked}>{everyoneIcon}</span> Everyone</div>
                    {groups}
                </div>
                {this.props.selected.length} Recipient{this.props.selected.length === 1 ? '' : 's'} Selected
            </div>
        );
    }
}


NewBroadcastRecipientsUi.propTypes = {
    groups: PropTypes.object.isRequired,
    selected: PropTypes.arrayOf(PropTypes.number).isRequired,

    init: PropTypes.func.isRequired,
    selectAllGroupMembers: PropTypes.func.isRequired,
    selectEveryone: PropTypes.func.isRequired,
    unselectAllGroupMembers: PropTypes.func.isRequired,
    unselectEveryone: PropTypes.func.isRequired,
    userClicked: PropTypes.func.isRequired
};

export const NewBroadcastRecipients = connect(mapNewBroadcastRecipientsProps, mapNewBroadcastRecipientsDispatch)(NewBroadcastRecipientsUi);
